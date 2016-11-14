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
        if ($category === 'genres') {
            $categories = $this->get('pps.categories_service')->findUsedGenres($category);
        } elseif ($category === 'formats') {
            $categories = $this->get('pps.categories_service')->findUsedFormats($category);
        } else {
            // This shouldn't really happen as the route only matches genres or formats, but better safe than sorry
            throw $this->createNotFoundException(sprintf("'%s' is not a valid category type", $category));
        }

        $topLevelCategories = [];
        $secondLevelCategories = [];

        // Separate between categories and subcategories
        foreach ($categories as $category) {
            if ($category instanceof Format || is_null($category->getParent())) {
                $topLevelCategories[] = $category;
            } else {
                $secondLevelCategories[] = $category;
            }
        }

        $mappedCategories = [];

        // The mapper is only called for top level categories, subcategories are passed as an additional parameter
        foreach ($topLevelCategories as $topLevelCategory) {
            $subcategories = [];

            // Separate a category's subcategories
            foreach ($secondLevelCategories as $subcategory) {
                if ($subcategory->getParent()->getId() === $topLevelCategory->getId()) {
                    $subcategories[] = $subcategory;
                }
            }

            // Map the category and its subcategories
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
