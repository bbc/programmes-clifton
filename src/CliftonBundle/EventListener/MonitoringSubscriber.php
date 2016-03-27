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
        ];
    }

    public function onRequestStart(GetResponseEvent $event)
    {
        $this->stopwatch->start('request');
    }

    public function onTerminate(PostResponseEvent $event)
    {
        $stopEvent = $this->stopwatch->stop('request');
        $status = $event->getResponse()->getStatusCode();

        // TODO: This currently makes a request per call to putMetricData. We
        // should give the MonitoringHandler a way to make multiple metric logs
        // in a single call (the underlying library supports this)
        // Only count requests where the user agent is not the ELB health checker
        if ($event->getRequest()->headers->get('User-Agent') != 'ELB-HealthChecker/1.0') {
            $this->monitor->putMetricData('RequestCount', 1, [], "Count");
            $this->monitor->putMetricData('RequestTime', $stopEvent->getDuration(), [], "Milliseconds");
        }

        if ($status == 400) {
            $this->monitor->putMetricData('400Error', 1, [], "Count");
        }

        if ($status == 500) {
            $this->monitor->putMetricData('500Error', 1, [], "Count");
        }

        $this->monitor->sendMetrics();
    }
}
