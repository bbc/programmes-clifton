<?php

namespace BBC\CliftonBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class DebugController extends BaseApsController
{
    /**
     * @ApiDoc()
     */
    public function debugAction(Request $request, $serviceName, $serviceMethod)
    {
        $rawArgs = $request->query->get('a', '[]');
        $args = json_decode($rawArgs);

        if (!is_array($args)) {
            throw $this->createNotFoundException(sprintf('Expected get parameter "a" to be a json encoded array. Got "%s"', $rawArgs));
        }

        $result = call_user_func_array(
            [$this->get('clifton.' . $serviceName), $serviceMethod],
            $args
        );

        ob_start();
        print_r($result);
        $dump = ob_get_clean();
        $output = '<html><body><pre>' . $dump . '</pre></body></html>';

        return new Response($output, 200, ['Content-Type' => 'text/html']);
    }
}
