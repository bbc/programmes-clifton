<?php

namespace BBC\CliftonBundle\Controller;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class ProgrammeChildrenController extends BaseApsController
{
    /**
     * @ApiDoc()
     */
    public function childrenAction(Request $request, string $pid): JsonResponse
    {
        $programmesService = $this->get('clifton.programmes_service');

        $pid = new Pid($pid);
        $limit = $this->queryParamToInt($request, 'limit', 30, 1, 999);
        $page = $this->queryParamToInt($request, 'page', 1, 1);

        $totalCount = $programmesService->countChildrenByPid($pid);

        // Only request children if there are any, to potentially save a query
        $programmesResult = [];
        if ($totalCount) {
            $programmesResult = $programmesService->findChildrenByPid($pid, $limit, $page)->getResult();
        }

        $apsChildren = $this->mapManyApsObjects(
            $this->get('clifton.programme_children_programme_mapper'),
            $programmesResult
        );

        return $this->jsonResponse(
            [
                'children' => [
                    'page' => $page,
                    'total' => $totalCount,
                    'offset' => $limit * ($page - 1),
                    'programmes' => $apsChildren,
                ],
            ]
        );
    }
}
