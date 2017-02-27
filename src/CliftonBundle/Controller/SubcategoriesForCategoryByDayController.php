<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\CategoryItemMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Category;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use BBC\ProgrammesPagesService\Service\BroadcastsService;
use BBC\ProgrammesPagesService\Service\CategoriesService;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;

class SubcategoriesForCategoryByDayController extends BaseApsController
{
    use Traits\CategoryFetchingTrait;

    public function showBroadcastForCategoryBySubcategoriesAction(
        Request $request,
        string $categoryType,
        string $urlKeyHierarchy,
        int $year,
        int $month,
        int $day
    ) {
        /** @var CategoriesService $categoriesService */
        $categoriesService = $this->get('pps.categories_service');
        /** @var BroadcastsService $broadcastService */
        $broadcastService = $this->get('pps.broadcasts_service');

        $categorySelected = $this->fetchCategoryFromTypeAndUrlHierarchy($categoryType, $urlKeyHierarchy);

        $broadcastedCategoriesAtScheduledDate = [];
        if ($categoryType === 'genres') {
            $subcategories = $categoriesService->findPopulatedChildGenres($categorySelected);
            $broadcastedCategoriesAtScheduledDate = $broadcastService->filterCategoriesByBroadcastedDate(
                $subcategories,
                new DateTimeImmutable("$year-$month-$day 00:00:01"),
                new DateTimeImmutable("$year-$month-$day 23:59:59")
            );
        }

        $categoryMapped = $this->mapSingleApsObject(
            new CategoryItemMapper(),
            $categorySelected,
            true, // mapBroader
            $broadcastedCategoriesAtScheduledDate
        );

        return $this->json(['category' => $categoryMapped]);
    }
}
