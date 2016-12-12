<?php

namespace BBC\CliftonBundle\Controller\Traits;

use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

trait CategoryFetchingTrait
{
    private function fetchCategoryFromTypeAndUrlHierarchy(
        string $categoryType,
        string $urlKeyHierarchy,
        int $depthLimit = 3
    ) {
        if (!is_subclass_of($this, 'BBC\CliftonBundle\Controller\BaseApsController')) {
            throw new InvalidTypeException(
                sprintf(
                    'This trait should only be used by a subclass of BaseApsController, instead got a subclass of %s',
                    get_parent_class($this)
                )
            );
        }

        $urlKeyHierarchy = $this->parseCategoriesFromRoute($urlKeyHierarchy, $depthLimit);

        $categoriesService = $this->get('pps.categories_service');

        switch ($categoryType) {
            case 'formats':
                if (count($urlKeyHierarchy) > 1) {
                    throw $this->createNotFoundException('Category not found');
                }

                $category = $categoriesService->findFormatByUrlKeyAncestry($urlKeyHierarchy[0]);
                break;

            case 'genres':
                $category = $categoriesService->findGenreByUrlKeyAncestry($urlKeyHierarchy);
                break;

            default:
                // This shouldn't really happen as the route only matches genres or formats, but better safe than sorry
                throw $this->createNotFoundException(sprintf("'%s' is not a valid category type", $categoryType));
        }

        // 404 if category wasn't found
        if (empty($category)) {
            throw $this->createNotFoundException('Category not found');
        }

        return $category;
    }

    private function parseCategoriesFromRoute(string $categories, int $depthLimit): array
    {
        $categories = explode('/', $categories);

        if (!is_array($categories)) {
            $categories = [$categories];
        }

        if (count($categories) > $depthLimit) {
            throw $this->createNotFoundException('Category not found');
        }

        return array_reverse($categories);
    }
}
