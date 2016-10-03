<?php

namespace BBC\CliftonBundle\Controller;

use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class BroadcastYearsAndMonthsController extends BaseApsController
{
    public function yearsAndMonthsAction(Request $request, string $pid): JsonResponse
    {
        $pid = new Pid($pid);

        // Only valid for Brands and Series
        $programme = $this->get('pps.programmes_service')->findByPid($pid, "ProgrammeContainer");
        if (!$programme) {
            throw $this->createNotFoundException('ProgrammeContainer Not Found');
        }

        $bs = $this->get('pps.broadcasts_service');
        $yearsAndMonths = $bs->findBroadcastYearsAndMonthsByProgramme($programme);

        $years = [];
        foreach ($yearsAndMonths as $year => $months) {
            $years[] = [
                'id' => $year,
                'months' => array_map(function ($month) {
                    return ['id' => $month];
                }, $months),
            ];
        }

        return $this->json([
            'filters' => [
                'years' => $years,
                'tags' => [], // Unused, leave empty
            ],
        ]);
    }
}
