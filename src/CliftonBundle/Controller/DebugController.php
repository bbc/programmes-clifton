<?php

namespace BBC\CliftonBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use ReflectionException;
use ReflectionMethod;
use TypeError;

class DebugController extends BaseApsController
{
    public function debugAction(Request $request, $serviceName, $serviceMethod)
    {
        // Not accessible on LIVE environments
        if ($this->getParameter('cosmos_environment') == 'live') {
            throw $this->createNotFoundException();
        }

        $rawArgs = $request->query->get('a', '[]');
        $args = json_decode($rawArgs);

        if (!is_array($args)) {
            throw $this->createNotFoundException(sprintf('Expected get parameter "a" to be a json encoded array. Got "%s"', $rawArgs));
        }

        try {
            $callable = [$this->get('clifton.' . $serviceName), $serviceMethod];
            $reflectionParams = $this->getParametersForCallable($callable);

            // Make sure passed in args are fewer than the number of allowed arguments
            $args = array_slice($args, 0, count($reflectionParams));

            // TODO catch exception when bad arguments are passed in

            // Look through the arg list and try and if there are any of type
            // pid then set them up
            $args = $this->castStringsToPids($args, $reflectionParams);

            $result = call_user_func_array($callable, $args);
            $output = '<html><body>' . $this->htmlDump($result) . '</body></html>';

            return new Response($output, 200, ['Content-Type' => 'text/html']);
        } catch (ServiceNotFoundException $e) {
            throw $this->createNotFoundException(sprintf('Service "%s" was not found', $serviceName));
        } catch (ReflectionException $e) {
            throw $this->createNotFoundException(sprintf('Method "%s" was not found in service "%s"', $serviceMethod, $serviceName));
        } catch (TypeError $e) {
            // If you pass in incorrectly type arguments to the service method
            throw $this->createNotFoundException($e->getMessage());
        }
    }

    private function htmlDump($variable)
    {
        $cloner = new VarCloner();
        $dumper = new HtmlDumper();
        $output = '';

        $dumper->dump(
            $cloner->cloneVar($variable),
            function ($line, $depth) use (&$output) {
                if ($depth >= 0) {
                    $output .= str_repeat('  ', $depth) . $line . "\n";
                }
            }
        );

        return $output;
    }

    private function getParametersForCallable($callable)
    {
        $reflectionMethod = new ReflectionMethod($callable[0], $callable[1]);
        return $reflectionMethod->getParameters();
    }

    private function castStringsToPids(array $args, array $reflectionParams)
    {
        $pidClassName = 'BBC\ProgrammesPagesService\Domain\ValueObject\Pid';
        return array_filter(array_map(function ($reflectionParam, $arg) use ($pidClassName) {
            if (is_string($arg) && $reflectionParam && (string) $reflectionParam->getType() == $pidClassName) {
                return new Pid($arg);
            }

            return $arg;
        }, $reflectionParams, $args));
    }
}
