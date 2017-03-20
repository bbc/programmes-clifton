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

        $latestBroadcast = $this->get('pps.collapsed_broadcasts_service')->findPastByProgramme($programme, 1);

        // Get only the first collapsed broadcast because the one we got from the service could potentially be split
        // into two or more
        $mappedBroadcasts = array_slice($this->mapManyApsObjects(new CollapsedBroadcastMapper(), $latestBroadcast), 0, 1);

        return $this->json(['broadcasts' => $mappedBroadcasts]);
    }
}
