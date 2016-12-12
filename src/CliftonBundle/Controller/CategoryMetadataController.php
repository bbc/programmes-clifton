<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\CategoryMetadataMapper;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use DateInterval;

class CategoryMetadataController extends BaseApsController
{
    public function showCategoryMetadataAction(
        Request $request,
        string $categoryType,
        string $category1,
        string $category2 = null,
        string $category3 = null,
        string $medium = null
    ): JsonResponse {
        $categoriesService = $this->get('pps.categories_service');

        switch ($categoryType) {
            case 'formats':
                if ($category2 || $category3) {
                    throw $this->createNotFoundException('Category not found');
                }
                $category = $categoriesService->findFormatByUrlKeyAncestry($category1);
                break;

            case 'genres':
                $category = $categoriesService->findGenreByUrlKeyAncestry(
                    array_values(array_filter([$category3, $category2, $category1]))
                );
                break;

            default:
                // This shouldn't really happen as the route only matches genres or formats, but better safe than sorry
                throw $this->createNotFoundException(sprintf("'%s' is not a valid category type", $categoryType));
        }

        // 404 if category wasn't found
        if (empty($category)) {
            throw $this->createNotFoundException('Category not found');
        }

        // There's no such thing as a format with a parent, so there are no subformats
        $subcategories = [];
        if ($categoryType === 'genres') {
            $subcategories = $categoriesService->findPopulatedChildGenres($category, $medium);
        }

        $programmesService = $this->get('pps.programmes_service');
        // APS only shows the first 5 results (programmes_categories/show.hash.data#L71)
        $episodes = $programmesService->findAvailableEpisodesByCategory($category, $medium, 5);
        $episodesCount = $programmesService->countAvailableEpisodesByCategory($category, $medium);

        $now = ApplicationTime::getTime();

        $collapsedBroadcastsService = $this->get('pps.collapsed_broadcasts_service');
        // APS only shows the first 5 results (programmes_categories/show.hash.data#L79)
        $upcomingBroadcasts = $collapsedBroadcastsService->findByCategoryAndEndAtDateRange(
            $category,
            $now,
            $now->add(new DateInterval('P31D')), // APS sets this 31 day limit in Models::Broadcast#L658
            $medium,
            5
        );

        $upcomingBroadcastsCount = $collapsedBroadcastsService->countByCategoryAndEndAtDateRange(
            $category,
            $now,
            $now->add(new DateInterval('P31D')), // APS sets this 31 day limit in Models::Broadcast#L658
            $medium
        );

        // APS has the hide_counts flag up, so we don't show counts for anything. So, we are not worrying about this.
        // Just to be safe, though, when we do ticket PROGRAMMES-5254 we will check if the frontend is being broken in
        // any way
        $counts = [];

        $categoryMetadataMapper = new CategoryMetadataMapper();
        return $this->json([
            'category_page' => $categoryMetadataMapper->getApsObject(
                $category,
                $subcategories,
                $episodes,
                $episodesCount,
                $upcomingBroadcasts,
                $upcomingBroadcastsCount,
                $counts,
                $medium
            ),
        ]);
    }
}
