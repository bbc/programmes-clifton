<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\ProgrammeChildrenProgrammeMapper;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProgrammeChildrenController extends BaseApsController
{
    public function childrenAction(Request $request, string $pid): JsonResponse
    {
        $pid = new Pid($pid);
        $limit = $this->queryParamToInt($request, 'limit', 30, 1, 999);
        $page = $this->queryParamToInt($request, 'page', 1, 1);

        $programmesService = $this->get('pps.programmes_service');

        $programmeDbId = $programmesService->findIdByPid($pid);

        if (is_null($programmeDbId)) {
            throw $this->createNotFoundException(sprintf('The item with PID "%s" was not found', $pid));
        }

        $totalCount = $programmesService->countEpisodeGuideChildrenByDbId($programmeDbId);

        // Only request children if there are any, to potentially save a query
        $programmesResult = [];
        if ($totalCount) {
            $programmesResult = $programmesService->findEpisodeGuideChildrenByDbId($programmeDbId, $limit, $page);
        }

        $apsChildren = $this->mapManyApsObjects(
            new ProgrammeChildrenProgrammeMapper(),
            $programmesResult
        );

        return $this->json([
            'children' => [
                'page' => $page,
                'total' => $totalCount,
                'offset' => $limit * ($page - 1),
                'programmes' => $apsChildren,
            ],
        ]);
    }
}
