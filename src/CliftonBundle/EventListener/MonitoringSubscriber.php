<?php

namespace BBC\CliftonBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Stopwatch\Stopwatch;
use RMP\CloudwatchMonitoring\MonitoringHandler;

class MonitoringSubscriber implements EventSubscriberInterface
{
    private $monitor;
    private $componentName;
    private $stopwatch;

    public function __construct(MonitoringHandler $monitor, Stopwatch $stopwatch, $componentName)
    {
        $this->monitor = $monitor;
        $this->stopwatch = $stopwatch;
        $this->componentName = $componentName;
    }

    public static function getSubscribedEvents()
    {
        return [
           'kernel.request' => [['onRequestStart', 1023]],
           'kernel.terminate' => [['onTerminate', -1023]],
           'kernel.exception' => [['onKernelException', 0]],
        ];
    }

    public function onRequestStart(GetResponseEvent $event)
    {
        $this->stopwatch->start('request');
    }

    public function onTerminate(PostResponseEvent $event)
    {
        $stopEvent = $this->stopwatch->stop('request');

        // TODO: This currently makes a request per call to putMetricData. We
        // should give the MonitoringHandler a way to make multiple metric logs
        // in a single call (the underlying library supports this)
        // Only count requests where the user agent is not the ELB health checker
        if ($event->getRequest()->headers->get('User-Agent') != 'ELB-HealthChecker/1.0') {
            $this->monitor->putMetricData('RequestCount', 1, [], "Count");
            $this->monitor->putMetricData('RequestTime', $stopEvent->getDuration(), [], "Milliseconds");
        }

        $this->monitor->sendMetrics();
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception) {
            if ($exception->getStatusCode() == 500) {
                $this->monitor->putMetricData('500Error', 1, [['Name' => 'ComponentName', 'Value' => $this->componentName]], "Count");
                // Don't need to call sendMetrics() here as it'll be logged
                // when we call sendMetrics in the Terminate handler
            }
        }
    }
}
