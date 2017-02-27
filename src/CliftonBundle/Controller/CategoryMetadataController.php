<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\CategoryMetadataMapper;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use DateInterval;

class CategoryMetadataController extends BaseApsController
{
    use Traits\CategoryFetchingTrait;

    public function showCategoryMetadataAction(
        Request $request,
        string $categoryType,
        string $urlKeyHierarchy
    ): JsonResponse {
        $category = $this->fetchCategoryFromTypeAndUrlHierarchy($categoryType, $urlKeyHierarchy);

        $categoriesService = $this->get('pps.categories_service');

        // There's no such thing as a format with a parent, so there are no subformats
        $subcategories = [];
        if ($categoryType === 'genres') {
            $subcategories = $categoriesService->findPopulatedChildGenres($category);
        }

        $programmesService = $this->get('pps.programmes_service');

        $episodesCount = $programmesService->countAvailableEpisodesByCategory($category);

        $collapsedBroadcastsService = $this->get('pps.collapsed_broadcasts_service');
        $now = ApplicationTime::getCurrent3MinuteWindow();

        $upcomingBroadcastsCount = $collapsedBroadcastsService->countByCategoryAndEndAtDateRange(
            $category,
            $now,
            $now->add(new DateInterval('P31D'))
        );

        // APS has the hide_counts flag up, so we don't show counts for anything. Therefore, we are not worrying about this.
        $counts = [];

        $categoryMetadataMapper = new CategoryMetadataMapper();
        return $this->json([
            'category_page' => $categoryMetadataMapper->getApsObject(
                $category,
                $subcategories,
                $episodesCount,
                $upcomingBroadcastsCount,
                $counts
            ),
        ]);
    }
}
