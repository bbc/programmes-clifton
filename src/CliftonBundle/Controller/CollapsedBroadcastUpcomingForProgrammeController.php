<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\CollapsedBroadcastMapper;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Symfony\Component\HttpFoundation\Request;

class CollapsedBroadcastUpcomingForProgrammeController extends BaseApsController
{
    public function collapsedBroadcastUpcomingForProgrammeAction(Request $request, string $pid)
    {
        $limit = $this->queryParamToInt($request, 'limit', 30, 1, 999);
        $page = $this->queryParamToInt($request, 'page', 1, 1, 99999);

        $pid = new Pid($pid);

        // Only valid for Brands and Series
        $programme = $this->get('pps.programmes_service')->findByPid($pid, "ProgrammeContainer");
        if (!$programme) {
            throw $this->createNotFoundException('Not Found');
        }

        $bs = $this->get('pps.collapsed_broadcasts_service');
        $totalCount = $bs->countUpcomingByProgramme($programme);

        if (!$totalCount) {
            throw $this->createNotFoundException('No Broadcasts Found');
        }

        $offset = $limit * ($page - 1);

        // offset is 0 indexed so if you've got 10 items total and you're
        // showing 10 items per page then for page 2, offset would be 10, which
        // should throw an error (as all the items are show on page 1)
        if ($offset >= $totalCount) {
            throw $this->createNotFoundException('Invalid page number');
        }

        $latestBroadcast = $bs->findUpcomingByProgramme($programme, $limit, $page);

        $mappedBroadcasts = $this->mapManyApsObjects(
            new CollapsedBroadcastMapper(),
            $latestBroadcast
        );

        return $this->json([
            'page' => $page,
            'total' => $totalCount,
            'offset' => $offset,
            'broadcasts' => $mappedBroadcasts,
        ]);
    }
}
