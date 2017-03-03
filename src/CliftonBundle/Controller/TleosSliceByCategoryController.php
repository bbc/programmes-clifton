<?php
namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\TleosSliceByCategoryMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Symfony\Component\HttpFoundation\Request;

class TleosSliceByCategoryController extends BaseApsController
{
    use Traits\CategoryFetchingTrait;

    public function showSliceTleosByCategoryAction(
        Request $request,
        string $categoryType,
        string $urlKeyHierarchy,
        string $slice
    ) {
        $limit = $this->queryParamToInt($request, 'limit', 3000, 1, 3000);
        $page = $this->queryParamToInt($request, 'page', 1, 1, 99999);

        if ($slice !== 'all' && $slice !== 'player') {
            throw  $this->createNotFoundException('Slice does not exist');
        }

        $category = $this->fetchCategoryFromTypeAndUrlHierarchy($categoryType, $urlKeyHierarchy);

        $subcategories = [];
        if ($category instanceof Genre) {
            $subcategories = $this->get('pps.categories_service')->findPopulatedChildGenres($category);
        }

        /** @var ProgrammesService $programmesService */
        $programmesService = $this->get('pps.programmes_service');

        if ($slice === 'all') {
            $count = $programmesService->countAllTleosByCategory($category);
        } else {
            $count = $programmesService->countAvailableTleosByCategory($category);
        }

        $offset = $limit * ($page - 1);
        if ($offset >= $count) {
            $this->createNotFoundException('Invalid page number');
        }

        if ($slice === 'all') {
            $programmes = $programmesService->findAllTleosByCategory($category, $limit, $page);
        } else {
            $programmes = $programmesService->findAvailableTleosByCategory($category, $limit, $page);
        }

        $mappedSlice = $this->mapSingleApsObject(new TleosSliceByCategoryMapper(), $programmes, $category, $slice, $subcategories);

        return $this->json((object) [
            'page' => $page,
            'total' => $count,
            'offset' => $offset,
            'category_slice' => $mappedSlice,
        ]);
    }
}
