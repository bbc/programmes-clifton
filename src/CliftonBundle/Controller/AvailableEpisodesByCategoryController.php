<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\AvailableEpisodesByCategoryMapper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AvailableEpisodesByCategoryController extends BaseApsController
{
    use Traits\CategoryFetchingTrait;

    public function availableEpisodesByCategoryAction(
        Request $request,
        string $categoryType,
        string $urlKeyHierarchy,
        string $medium = null
    ): JsonResponse {
        $category = $this->fetchCategoryFromTypeAndUrlHierarchy($categoryType, $urlKeyHierarchy);

        $limit = $this->queryParamToInt($request, 'limit', 30, 1, 999);
        $page = $this->queryParamToInt($request, 'page', 1, 1, 99999);
        $offset = $limit * ($page - 1);

        $programmesService = $this->get('pps.programmes_service');

        // We need to count first to get the total amount
        $totalCount = $programmesService->countAvailableEpisodesByCategory($category, $medium);

        if ($offset >= $totalCount) {
            throw $this->createNotFoundException('Invalid page number');
        }

        if (!$totalCount) {
            throw $this->createNotFoundException('No episodes found');
        }

        $episodes = $programmesService->findAvailableEpisodesByCategory($category, $medium, $limit, $page);

        $mappedEpisodes = $this->mapManyApsObjects(new AvailableEpisodesByCategoryMapper(), $episodes);

        return $this->json([
            'page' => $page,
            'total' => $totalCount,
            'offset' => $offset,
            'episodes' => $mappedEpisodes,
        ]);
    }
}
