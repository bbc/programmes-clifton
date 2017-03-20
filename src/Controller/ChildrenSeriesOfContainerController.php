<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\ChildrenSeriesOfContainerMapper;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ChildrenSeriesOfContainerController extends BaseApsController
{
    public function childrenSeriesOfContainerAction(Request $request, string $pid): JsonResponse
    {
        $pid = new Pid($pid);
        $programmesService = $this->get('pps.programmes_service');

        // Only valid for Brands and Series
        $programme = $programmesService->findByPid($pid, "ProgrammeContainer");

        if (empty($programme)) {
            throw $this->createNotFoundException('Not Found');
        }

        $programmeContainers = $programmesService
            ->findChildrenSeriesByParent($programme, $programmesService::NO_LIMIT);

        if (empty($programmeContainers)) {
            throw $this->createNotFoundException('Not Found');
        }

        $series = $this->mapManyApsObjects(new ChildrenSeriesOfContainerMapper(), $programmeContainers);

        return $this->json(['programmes' => $series]);
    }
}
