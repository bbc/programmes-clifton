<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\AvailableEpisodesByCategoryMapper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AvailableEpisodesByCategoryController extends BaseApsController
{
    public function availableEpisodesByCategoryAction(Request $request, string $medium, string $categoryType, string $category1, string $category2, string $category3): JsonResponse
    {
        $limit = $this->queryParamToInt($request, 'limit', 30, 1, 999);
        $page = $this->queryParamToInt($request, 'page', 1, 1, 99999);
        $offset = $limit * ($page - 1);

        $categoriesService = $this->get('pps.categories_service');
        $programmesService = $this->get('pps.programmes_service');

        switch ($categoryType) {
            case 'formats':
                if ($category2 || $category3) {
                    throw $this->createNotFoundException('Category not found');
                }
                $categories = $categoriesService->findFormatByUrlKeyAncestry($category1);
                break;

            case 'genres':
                $categories = $categoriesService->findGenreByUrlKeyAncestry(
                    array_values(array_filter([$category3, $category2, $category1]))
                );
                break;

            default:
                // This shouldn't really happen as the route only matches genres or formats, but better safe than sorry
                throw $this->createNotFoundException(sprintf("'%s' is not a valid category type", $categoryType));
        }

        if (!$categories) {
            throw $this->createNotFoundException('Category not found');
        }

        // We need to count first to get the total amount
        $totalCount = $programmesService->countAvailableEpisodesByCategory(
            $categories,
            $medium
        );

        if ($offset >= $totalCount) {
            throw $this->createNotFoundException('Invalid page number');
        }

        if (!$totalCount) {
            throw $this->createNotFoundException('No episodes found');
        }

        $episodes = $programmesService->findAvailableEpisodesByCategory(
            $categories,
            $medium,
            $limit,
            $page
        );

        $mappedEpisodes = $this->mapManyApsObjects(
            new AvailableEpisodesByCategoryMapper(),
            $episodes
        );

        return $this->json([
            'page' => $page,
            'total' => $totalCount,
            'offset' => $offset,
            'episodes' => $mappedEpisodes,
        ]);
    }
}
