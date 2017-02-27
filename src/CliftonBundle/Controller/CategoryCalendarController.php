<?php

namespace BBC\CliftonBundle\Controller;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CategoryCalendarController extends BaseApsController
{
    use Traits\CategoryFetchingTrait;

    public function categoryCalendarAction(
        Request $request,
        string $categoryType,
        string $urlKeyHierarchy,
        int $year,
        int $month
    ): JsonResponse {
        $category = $this->fetchCategoryFromTypeAndUrlHierarchy($categoryType, $urlKeyHierarchy);

        $date = DateTimeImmutable::createFromFormat(
            'j-m-Y',
            sprintf('%s-%s-%s', 1, $month, $year),
            new DateTimeZone("Europe/London")
        )->setTime(0, 0, 0);

        $startLastMonth = $date->sub(new DateInterval('P1M'));
        $endNextMonth = $date->add(new DateInterval('P2M'));

        $lastMonth = (int) $startLastMonth->format('m');
        $lastMonthsYear = (int) $startLastMonth->format('Y');

        $nextMonth = (int) $date->add(new DateInterval('P1M'))->format('m');
        $nextMonthsYear = (int) $date->add(new DateInterval('P1M'))->format('Y');

        $currentMonth = (int) $date->format('m');
        $currentMonthsYear = (int) $date->format('Y');

        $categoryService = $this->get('pps.broadcasts_service');
        $dates = $categoryService->findDaysByCategoryInDateRange(
            $category,
            $startLastMonth,
            $endNextMonth
        );

        return $this->json((object) [
            'month' => (object) [
                'date' => $date->format('Y-m-d'),
                // This isn't actually used anywhere, and APS sets a wrong value for this, but we're keeping it to make
                // the feeds equal. We're using the expected behaviour, though. Same for 'has_next_month'.
                'has_previous_month' => !empty($dates[$lastMonthsYear][$lastMonth]),
                'has_next_month' => !empty($dates[$nextMonthsYear][$nextMonth]),
                'active_days' => (object) [
                    'previous_month' => $dates[$lastMonthsYear][$lastMonth] ?? [],
                    'this_month' => $dates[$currentMonthsYear][$currentMonth] ?? [],
                    'next_month' => $dates[$nextMonthsYear][$nextMonth] ?? [],
                ],
            ],
        ]);
    }
}
