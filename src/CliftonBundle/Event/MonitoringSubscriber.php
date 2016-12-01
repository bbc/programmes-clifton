<?php

namespace BBC\CliftonBundle\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\Stopwatch\Stopwatch;
use RMP\CloudwatchMonitoring\MonitoringHandler;

class MonitoringSubscriber implements EventSubscriberInterface
{
    const REQUEST_TIMER = 'aps.request_time';

    private $monitor;
    private $stopwatch;

    public function __construct(MonitoringHandler $monitor, Stopwatch $stopwatch)
    {
        $this->monitor = $monitor;
        $this->stopwatch = $stopwatch;
    }

    public static function getSubscribedEvents()
    {
        return [
            // Start timer
            KernelEvents::REQUEST => [['requestStart', 512]],
            // Stop timer and log the duration
            KernelEvents::TERMINATE => [['terminateEnd', 0]],
        ];
    }

    public function requestStart(KernelEvent $event)
    {
        if ($event->isMasterRequest()) {
            $this->stopwatch->start(self::REQUEST_TIMER, 'section');
        }
    }

    public function terminateEnd(KernelEvent $event)
    {
        $this->logRequestTime($event);

        $this->monitor->sendMetrics();
    }

    private function logRequestTime(KernelEvent $event)
    {
        if ($event->isMasterRequest()) {
            $this->stopwatch->stop(self::REQUEST_TIMER);
        }

        $controllerAction = $event->getRequest()->attributes->get('_controller', '');

        // Skip if we can't find a controller, or if it isn't a Clifton Controller
        if (!$controllerAction || strpos($controllerAction, 'BBC\CliftonBundle\Controller') === false) {
            return;
        }

        // Strip off the common preamble for the sake of readability
        $controllerAction = str_replace('BBC\\CliftonBundle\\Controller\\', '', $controllerAction);

        // Skip if it is the status controller
        // This gets pinged every 15 seconds by the ELB and we don't need that noise
        if ($controllerAction == 'StatusController::statusAction') {
            return;
        }

        $controllerPeriod = $this->getControllerPeriod();
        if ($controllerPeriod) {
            $this->monitor->putMetricData('ControllerActionRenderCount', 1, [
                ['Name' => 'ControllerAction', 'Value' => $controllerAction],
            ], 'Count');

            $this->monitor->putMetricData('ControllerActionRenderTime', $controllerPeriod, [
                ['Name' => 'ControllerAction', 'Value' => $controllerAction],
            ], 'Milliseconds');
        }
    }

    private function getControllerPeriod()
    {
        foreach ($this->stopwatch->getSections() as $section) {
            $event = $section->getEvents()[self::REQUEST_TIMER] ?? null;
            if ($event) {
                return $event->getDuration();
            }
        }

        return null;
    }
}
