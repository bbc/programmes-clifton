<?php
namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\CollapsedBroadcastMapper;
use BBC\ProgrammesPagesService\Service\AbstractService;
use DateTimeImmutable;
use DateTimeZone;
use DateInterval;
use Symfony\Component\HttpFoundation\Request;

class BroadcastForCategoryByDayController extends BaseApsController
{
    use Traits\CategoryFetchingTrait;

    public function showBroadcastForCategoryByDayAction(
        Request $request,
        string $categoryType,
        string $urlKeyHierarchy,
        int $year,
        int $month,
        int $day
    ) {
        $collapsedBroadcastService = $this->get('pps.collapsed_broadcasts_service');

        $category = $this->fetchCategoryFromTypeAndUrlHierarchy($categoryType, $urlKeyHierarchy);

        // get emission period (from 0.00 until 5.00 am of next day) (Controller::ProgrammesCategories#L1099)
        // aps use localtime: (Model::Date#L29)
        $dateSelected = DateTimeImmutable::createFromFormat(
            'j-m-Y',
            sprintf('%s-%s-%s', $day, $month, $year),
            new DateTimeZone("Europe/London")
        );

        $fromDate = $dateSelected->setTime(0, 0, 0);
        $toDate = $dateSelected->add(new DateInterval('P1D'));
        $toDate = $toDate->setTime(5, 0, 0);

        $broadcasts = $collapsedBroadcastService->findByCategoryAndStartAtDateRange(
            $category,
            $fromDate,
            $toDate,
            AbstractService::NO_LIMIT
        );

        if (empty($broadcasts)) {
            throw  $this->createNotFoundException('Schedule not found');
        }

        $broadcastForCategoryByDay = $this->mapManyApsObjects(new CollapsedBroadcastMapper(), $broadcasts);

        return $this->json(
            ['broadcasts' => $broadcastForCategoryByDay]
        );
    }
}
