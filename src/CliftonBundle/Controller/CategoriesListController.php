<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\CategoryItemMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Format;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CategoriesListController extends BaseApsController
{
    public function panBbcCategoriesListAction(Request $request, string $category): JsonResponse
    {
        /** @var Format[]|Genre[] $categories */
        $categories = $this->get('pps.categories_service')->findUsedByType($category);

        $topLevelCategories = [];
        $secondLevelCategories = [];

        foreach ($categories as $category) {
            if ($category instanceof Format || is_null($category->getParent())) {
                $topLevelCategories[] = $category;
            } else {
                $secondLevelCategories[] = $category;
            }
        }

        $mappedCategories = [];

        foreach ($topLevelCategories as $topLevelCategory) {
            $subcategories = [];

            foreach ($secondLevelCategories as $subcategory) {
                if ($subcategory->getParent()->getId() === $topLevelCategory->getId()) {
                    $subcategories[] = $subcategory;
                }
            }

            $mappedCategories[] = $this->mapSingleApsObject(
                new CategoryItemMapper(),
                $topLevelCategory,
                true,
                $subcategories
            );
        }

        return $this->json(['categories' => $mappedCategories]);
    }
}
