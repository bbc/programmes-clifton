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
        $category = $this->fetchCategoryFromTypeAndUrlHierarchy($categoryType, $urlKeyHierarchy);

        $subcategories = [];
        if ($category instanceof Genre) {
            $subcategories = $this->get('pps.categories_service')->findPopulatedChildGenres($category);
        }

        /** @var ProgrammesService $programmesService */
        $programmesService = $this->get('pps.programmes_service');
        switch ($slice) {
            case 'all':
                $programmes = $programmesService->findAllTleosByCategory($category, 3000);
                break;

            case 'player':
                $programmes = $programmesService->findAvailableTleosByCategory($category, 3000);
                break;
            default:
                throw  $this->createNotFoundException("Slice does not exist");
        }

        $mapper = new TleosSliceByCategoryMapper();

        return $this->json($mapper->getApsObject(
            $programmes,
            $category,
            $slice,
            $subcategories
        ));
    }
}
