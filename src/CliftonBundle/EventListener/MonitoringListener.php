<?php

namespace BBC\CliftonBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use RMP\CloudwatchMonitoring\MonitoringHandler;

class MonitoringListener
{
    private $monitor;
    private $componentName;

    public function __construct(MonitoringHandler $monitor, $componentName)
    {
        $this->monitor = $monitor;
        $this->componentName = $componentName;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception) {
            if (method_exists($exception, 'getStatusCode')) {
                if ($exception->getStatusCode() == 500) {
                    $this->monitor->putMetricData('500Error', 1, [['Name' => 'ComponentName', 'Value' => $this->componentName]]);
                    $this->monitor->sendMetrics();
                }
            } else {
                $this->monitor->putMetricData('500Error', 1, [['Name' => 'ComponentName', 'Value' => $this->componentName]]);
                $this->monitor->sendMetrics();
            }

        }
    }
}
