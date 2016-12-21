<?php

namespace BBC\CliftonBundle\ApsMapper;

class CategoryMetadataMapper implements MapperInterface
{
    use Traits\CategoryItemTrait;
    use Traits\CollapsedBroadcastTrait;
    use Traits\ServiceTrait;

    public function getApsObject(
        $category,
        array $subcategories = null,
        array $availableEpisodes = null,
        int $availableEpisodesCount = 0,
        array $upcomingBroadcasts = null,
        int $upcomingBroadcastsCount = 0,
        array $counts = [],
        string $medium = null
    ) {
        $output = [
            'category' => $this->mapCategoryItem($category, true, $subcategories),
            'service' => $this->mapMediumService($medium),
            'available_programmes_count' => $availableEpisodesCount,
            'available_programmes' => $availableEpisodes ?
                array_map(
                    function ($episode) {
                        return $this->mapEpisodeItem($episode, true);
                    },
                    $availableEpisodes
                ) :
                $availableEpisodes,
            'upcoming_broadcasts_count' => $upcomingBroadcastsCount,
            'upcoming_broadcasts' => $upcomingBroadcasts ?
                array_map([$this, 'mapCollapsedBroadcast'], $upcomingBroadcasts) :
                $upcomingBroadcasts,
            'available_and_upcoming_counts' => $counts,
        ];

        if (!$medium) {
            unset($output['service']);
        }

        return (object) $output;
    }
}
