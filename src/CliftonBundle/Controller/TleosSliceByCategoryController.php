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
        string $slice,
        string $medium = null
    ) {
        $category = $this->fetchCategoryFromTypeAndUrlHierarchy($categoryType, $urlKeyHierarchy);

        $subcategories = [];
        if ($category instanceof Genre) {
            $subcategories = $this->get('pps.categories_service')->findPopulatedChildGenres($category, $medium);
        }

        /** @var ProgrammesService $programmesService */
        $programmesService = $this->get('pps.programmes_service');
        switch ($slice) {
            case 'all':
                $programmes = $programmesService->findAllTleosByCategory($category, $medium, null);
                break;

            case 'player':
                $programmes = $programmesService->findAvailableTleosByCategory($category, $medium, null);
                break;
            default:
                throw  $this->createNotFoundException("Slice does not exist");
        }

        $mapper = new TleosSliceByCategoryMapper();

        return $this->json($mapper->getApsObject(
            $programmes,
            $medium,
            $category,
            $slice,
            $subcategories
        ));
    }
}
