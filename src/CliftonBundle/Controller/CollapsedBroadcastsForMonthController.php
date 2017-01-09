<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\CollapsedBroadcastMapper;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CollapsedBroadcastsForMonthController extends BaseApsController
{
    public function collapsedBroadcastsForMonthAction(
        Request $request,
        string $pid,
        string $year,
        string $month
    ): JsonResponse {
        $pid = new Pid($pid);

        // Only valid for Brands and Series
        $programme = $this->get('pps.programmes_service')->findByPid($pid, "ProgrammeContainer");
        if (!$programme) {
            throw $this->createNotFoundException('Not Found');
        }

        $broadcastsByMonth = $this->get('pps.collapsed_broadcasts_service')
            ->findByProgrammeAndMonth($programme, $year, $month, CollapsedBroadcastsService::NO_LIMIT);
        if (empty($broadcastsByMonth)) {
            throw $this->createNotFoundException('Not Found');
        }

        $mappedBroadcasts = $this->mapManyApsObjects(new CollapsedBroadcastMapper(), $broadcastsByMonth);

        return $this->json(['broadcasts' => $mappedBroadcasts]);
    }
}
