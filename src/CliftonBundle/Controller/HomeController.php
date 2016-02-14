<?php

namespace BBC\CliftonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function homeAction(Request $request)
    {
        return $this->render('CliftonBundle:Home:home.html.twig', [
            'release_number' => $this->getParameter('cosmos_component_release'),
        ]);
    }
}
