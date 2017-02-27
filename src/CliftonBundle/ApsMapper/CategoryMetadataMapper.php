<?php

namespace BBC\CliftonBundle\ApsMapper;

class CategoryMetadataMapper implements MapperInterface
{
    use Traits\CategoryItemTrait;

    public function getApsObject(
        $category,
        array $subcategories = null,
        int $availableEpisodesCount = 0,
        int $upcomingBroadcastsCount = 0,
        array $counts = []
    ) {
        $output = [
            'category' => $this->mapCategoryItem($category, true, $subcategories),
            'available_programmes_count' => $availableEpisodesCount,
            // /programmes does not need the latest available episodes from this feed and they are
            // very expensive to build, so we represent them as empty
            'available_programmes' => null,
            'upcoming_broadcasts_count' => $upcomingBroadcastsCount,
            // /programmes does not need the latest upcoming from this feed and they are
            // very expensive to build, so we represent them as empty
            'upcoming_broadcasts' => null,
            'available_and_upcoming_counts' => $counts,
        ];


        return (object) $output;
    }
}
