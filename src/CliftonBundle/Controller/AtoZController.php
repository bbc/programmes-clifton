<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\AtoZItemMapper;
use BBC\ProgrammesPagesService\Domain\Entity\AtoZTitle;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Service\AtoZService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AtoZController
 *
 * Yes this is crazy. APS is crazy. Do you expect me to replicate insanity by being sane?
 * JE SUIS NAPOLEON Y'ALL
 *
 * @package BBC\CliftonBundle\Controller
 */
class AtoZController extends BaseApsController
{
    public function lettersListAction(Request $request, string $network)
    {
        $data = $this->getLettersAndSlice('player', $network);
        $data['tleo_titles'] = [];
        return $this->json([
            'atoz' => $data,
        ]);
    }

    public function byAction(Request $request, string $search, string $slice = 'player', string $network = null)
    {
        list ($page, $limit) = $this->filterParams($request);
        if (!$limit) {
            $limit = null;
        }

        if (strlen($search) < 2) {
            list ($items, $itemCount) = $this->letterSearch($search, $slice, $network, $page, $limit);
        } else {
            list ($items, $itemCount) = $this->keywordSearch($search, $slice, $network, $page, $limit);
        }

        $data = $this->getLettersAndSlice($slice, $network, $search, $page, $limit, $itemCount);
        $data['tleo_titles'] = $this->mapManyApsObjects(
            new AtoZItemMapper(),
            $items
        );
        return $this->json([
            'atoz' => $data,
        ]);
    }

    private function letterSearch(string $search, string $slice, string $network = null, int $page = null, int $limit = null)
    {
        $service = $this->get('pps.atoz_service');
        if (!$limit) {
            $limit = $service::NO_LIMIT;
        }
        $items = [];
        $itemCount = null;
        if ($slice == 'player') {
            $items = $service->findAvailableTLEOsByFirstLetter($search, $network, $limit, $page);
            if ($limit) {
                $itemCount = $service->countAvailableTLEOsByFirstLetter($search, $network);
            }
        } elseif ($slice == 'all') {
            $items = $service->findTLEOsByFirstLetter($search, $network, $limit, $page);
            if ($limit) {
                $itemCount = $service->countTLEOsByFirstLetter($search, $network);
            }
        } else {
            throw new NotFoundHttpException("Slice does not exist");
        }

        return [$items, $itemCount];
    }

    private function keywordSearch(string $search, string $slice, string $network = null, int $page = null, int $limit = null)
    {
        $service = $this->get('pps.programmes_service');
        if (!$limit) {
            $limit = $service::NO_LIMIT;
        }

        $items = [];
        $itemCount = null;
        if ($slice == 'player') {
            $items = $service->searchAvailableByKeywords($search, $network, $limit, $page);
            if ($limit) {
                $itemCount = $service->countAvailableByKeywords($search, $network);
            }
        } elseif ($slice == 'all') {
            $items = $service->searchByKeywords($search, $network, $limit, $page);
            if ($limit) {
                $itemCount = $service->countByKeywords($search, $network);
            }
        } else {
            throw new NotFoundHttpException("Slice does not exist");
        }
        // Make a-z titles out of core entities. Bit nuts...
        $fakeItems = [];
        foreach ($items as $item) {
            $firstLetter = substr($item->getTitle(), 0, 1);
            $fakeItems[] = new AtoZTitle($item->getTitle(), strtolower($firstLetter), $item);
        }

        return [$fakeItems, $itemCount];
    }

    private function getLettersAndSlice(
        string $slice,
        string $networkUrlKey = null,
        string $search = null,
        int $page = null,
        int $limit = null,
        int $total = null
    ) {
        $service = $this->get('pps.atoz_service');
        $offset = null;
        if ($limit) {
            $offset = ($page - 1) * $limit;
        }
        $data = [
            'slice' => $slice,
            'by' => null,
            'search' => $search,
            'service_group' => [],
            'letters' => $service->findAllLetters($networkUrlKey),
            'page' => $limit ? $page : null,
            'total' => $total,
            'offset' => $offset,
        ];
        if ($networkUrlKey) {
            $data['service_group'] = [
                'key' => $networkUrlKey,
                'title' => ($networkUrlKey == 'radio' ? 'BBC Radio' : 'BBC TV'),
            ];
        } else {
            unset($data['service_group']);
        }
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
