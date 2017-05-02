<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\AtozItemMapper;
use BBC\ProgrammesPagesService\Domain\Entity\AtozTitle;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AtozController extends BaseApsController
{
    public function lettersListAction(Request $request)
    {
        $data = $this->getLettersAndSlice('player');
        $data['tleo_titles'] = [];
        return $this->json([
            'atoz' => $data,
        ]);
    }

    public function byAction(Request $request, string $search, string $slice = 'player')
    {
        list ($page, $limit) = $this->filterParams($request);
        if (!$limit) {
            $limit = null;
        }

        if (strlen($search) < 2) {
            list ($items, $itemCount) = $this->letterSearch($search, $slice, $page, $limit);
        } else {
            list ($items, $itemCount) = $this->keywordSearch($search, $slice, $page, $limit);
        }

        if (!count($items)) {
            throw $this->createNotFoundException('No results returned');
        }

        $data = $this->getLettersAndSlice($slice, $search, $page, $limit, $itemCount);
        $data['tleo_titles'] = $this->mapManyApsObjects(
            new AtozItemMapper(),
            $items
        );
        return $this->json([
            'atoz' => $data,
        ]);
    }

    private function letterSearch(string $search, string $slice, int $page = null, int $limit = null)
    {
        $service = $this->get('pps.atoz_titles_service');
        if (!$limit) {
            $limit = $service::NO_LIMIT;
        }
        $items = [];
        $itemCount = null;
        if ($slice == 'player') {
            $items = $service->findAvailableTleosByFirstLetter($search, $limit, $page);
            if ($limit) {
                $itemCount = $service->countAvailableTleosByFirstLetter($search);
            }
        } elseif ($slice == 'all') {
            $items = $service->findTleosByFirstLetter($search, $limit, $page);
            if ($limit) {
                $itemCount = $service->countTleosByFirstLetter($search);
            }
        } else {
            throw new NotFoundHttpException("Slice does not exist");
        }

        return [$items, $itemCount];
    }

    private function keywordSearch(string $search, string $slice, int $page = null, int $limit = null)
    {
        $service = $this->get('pps.programmes_service');
        if (!$limit) {
            $limit = $service::NO_LIMIT;
        }

        $items = [];
        $itemCount = null;
        if ($slice == 'player') {
            $items = $service->searchAvailableByKeywords($search, $limit, $page);
            if ($limit) {
                $itemCount = $service->countAvailableByKeywords($search);
            }
        } elseif ($slice == 'all') {
            $items = $service->searchByKeywords($search, $limit, $page);
            if ($limit) {
                $itemCount = $service->countByKeywords($search);
            }
        } else {
            throw new NotFoundHttpException("Slice does not exist");
        }
        // Make a-z titles out of core entities. Bit nuts...
        $fakeItems = [];
        foreach ($items as $item) {
            $firstLetter = '@';
            $possibleAlphas = preg_replace('/[^A-Za-z0-9]/', '', $item->getTitle());
            if ($possibleAlphas) {
                $possibleFirstLetter = substr($possibleAlphas, 0, 1);
                if (preg_match('/^[A-Za-z]/', $possibleFirstLetter)) {
                    $firstLetter = strtolower($possibleFirstLetter);
                }
            }
            $fakeItems[] = new AtozTitle($item->getTitle(), $firstLetter, $item);
        }

        return [$fakeItems, $itemCount];
    }

    private function getLettersAndSlice(
        string $slice,
        string $search = null,
        int $page = null,
        int $limit = null,
        int $total = null
    ) {
        $service = $this->get('pps.atoz_titles_service');
        $offset = null;
        if ($limit) {
            $offset = ($page - 1) * $limit;
        }
        $data = [
            'slice' => $slice,
            'by' => null,
            'search' => $search,
            'letters' => $service->findAllLetters(),
            'page' => $limit ? $page : null,
            'total' => $total,
            'offset' => $offset,
        ];
        if ($search) {
            unset($data['by']);
        } else {
            unset($data['search']);
        }
        return $data;
    }

    private function filterParams(Request $request)
    {
        $inputPage = $request->query->filter(
            'page',
            null,
            FILTER_VALIDATE_INT,
            [ 'options' => ['min_range' => 1, 'max_range' => 99999] ]
        );
        $inputLimit = $request->query->filter(
            'limit',
            null,
            FILTER_VALIDATE_INT,
            [ 'options' => ['min_range' => 1, 'max_range' => 999] ]
        );
        $limit = null;
        $page = 1;
        if ($inputPage && !$inputLimit) {
            $inputLimit = 50;
        }
        if ($inputPage && $inputLimit) {
            // Limit is only valid with a page (APS behaviour)
            $page = (int) $inputPage;
            $limit = (int) $inputLimit;
        }
        return [$page, $limit];
    }
}
