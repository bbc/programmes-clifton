<?php

namespace BBC\CliftonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StatusController
 *
 * @package BBC\CliftonBundle\Controller
 */
class StatusController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function statusAction(Request $request)
    {
        // If the load balancer is pinging us then give them a plain OK
        if ($request->headers->get('User-Agent') == 'ELB-HealthChecker/1.0') {
            return new Response('OK', Response::HTTP_OK, ['content-type' => 'text/plain']);
        }

        // Other people get a better info screen
        $statusRepo = $this->getDoctrine()->getRepository('ProgrammesPagesService:Status');

        $status = $statusRepo->find(1);

        $pipsLag = $status->getPipsLatestTime()->diff(
            $status->getLatestChangeEventCreatedAt()
        );

        $formattedPipsLag = $pipsLag->format('%y Years, %m Months, %d Days, %h Hours, %i Minutes, %s Seconds');


        $progsRepo = $this->getDoctrine()->getRepository('ProgrammesPagesService:CoreEntity');
        $total = $progsRepo->countAll();

        $state = 'Stopped!';
        $color = 'red';
        $latestProcessed = $status->getLatestChangeEventProcessedAt();
        $now = new \DateTimeImmutable();
        $seconds = $now->getTimestamp() - $latestProcessed->getTimestamp();

        // if the last PIPs change is our change, we're paused
        if ($status->getLatestChangeEventId() == $status->getPipsLatestId()) {
            $state = "Paused - Caught up";
            $color = 'orange';
        } elseif ($seconds < 5) {
            // it's running if the last change was within 5 seconds,
            $state = 'Running';
            $color = 'green';
        }

        return $this->render('CliftonBundle:Status:status.html.twig', [
            'color' => $color,
            'state' => $state,
            'status' => $status,
            'pipsLag' => $formattedPipsLag,
            'total' => $total,
        ]);
    }
}
