<?php

namespace BBC\CliftonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DateTime;

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
        $dbalConnection = $this->get('doctrine.dbal.default_connection');

        return $this->render('CliftonBundle:Status:status.html.twig', [
            'now' => new DateTime(),
            'dbConnectivity' => $dbalConnection->isConnected() || $dbalConnection->connect(),
        ]);
    }
}
