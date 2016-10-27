<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\CollapsedBroadcastMapper;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Symfony\Component\HttpFoundation\Request;

class CollapsedBroadcastLatestForProgrammeController extends BaseApsController
{
    public function collapsedBroadcastLatestForProgrammeAction(Request $request, string $pid)
    {
        $pid = new Pid($pid);

        // Only valid for Brands and Series
        $programme = $this->get('pps.programmes_service')->findByPid($pid, "ProgrammeContainer");
        if (!$programme) {
            throw $this->createNotFoundException('Not Found');
        }

        $bs = $this->get('pps.collapsed_broadcasts_service');
        $latestBroadcast = $bs->findPastByProgramme($programme, 1);

        $mappedBroadcasts = $this->mapManyApsObjects(new CollapsedBroadcastMapper(), $latestBroadcast);

        return $this->json(['broadcasts' => $mappedBroadcasts]);
    }
}
