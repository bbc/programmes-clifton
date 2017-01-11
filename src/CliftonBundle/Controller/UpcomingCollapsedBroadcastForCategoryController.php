<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\CollapsedBroadcastMapper;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use DateInterval;
use Symfony\Component\HttpFoundation\Request;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;

class UpcomingCollapsedBroadcastForCategoryController extends BaseApsController
{
    use Traits\CategoryFetchingTrait;

    public function upcomingCollapsedBroadcastForCategoryAction(
        Request $request,
        string $categoryType,
        string $urlKeyHierarchy,
        string $medium = null
    ) {
        $limit = $this->queryParamToInt($request, 'limit', 30, 1, 999);
        $page = $this->queryParamToInt($request, 'page', 1, 1, 99999);

        $category = $this->fetchCategoryFromTypeAndUrlHierarchy($categoryType, $urlKeyHierarchy);

        /** @var CollapsedBroadcastsService $collapsedBroadcastsService */
        $collapsedBroadcastsService = $this->get('pps.collapsed_broadcasts_service');

        $now = ApplicationTime::getTime();

        $upcomingBroadcastsCount = $collapsedBroadcastsService->countByCategoryAndEndAtDateRange(
            $category,
            $now,
            $now->add(new DateInterval('P31D')), // APS sets this 31 day limit in Models::Broadcast#L658
            $medium
        );

        $offset = $limit * ($page - 1);
        if ($offset >= $upcomingBroadcastsCount) {
            throw $this->createNotFoundException('Invalid page number');
        }

        $upcomingBroadcasts = $collapsedBroadcastsService->findByCategoryAndEndAtDateRange(
            $category,
            $now,
            $now->add(new DateInterval('P31D')), // APS sets this 31 day limit in Models::Broadcast#L658
            $medium,
            $limit,
            $page
        );


        if (!$upcomingBroadcastsCount) {
            throw $this->createNotFoundException('No episodes found');
        }

        $mappedBroadcasts = $this->mapManyApsObjects(new CollapsedBroadcastMapper(), $upcomingBroadcasts);

        return $this->json([
            'page' => $page,
            'total' => $upcomingBroadcastsCount,
            'offset' => $offset,
            'broadcasts' => $mappedBroadcasts,
        ]);
    }
}
