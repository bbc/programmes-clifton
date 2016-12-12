<?php

namespace BBC\CliftonBundle\ApsMapper;

use InvalidArgumentException;

class CategoryMetadataMapper implements MapperInterface
{
    use Traits\CategoryItemTrait;
    use Traits\CollapsedBroadcastTrait;

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
            'service' => $medium ? $this->getService($medium) : $medium,
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

    private function getService($medium)
    {
        if ($medium === 'tv') {
            return (object) [
                'key' => 'tv',
                'id' => 'tv',
                'title' => 'BBC TV',
            ];
        } elseif ($medium === 'radio') {
            return (object) [
                'key' => 'radio',
                'id' => 'radio',
                'title' => 'BBC Radio',
            ];
        } else {
            throw new InvalidArgumentException(
                sprintf("The service must be either 'tv' or 'radio', instead got '%s'", $medium)
            );
        }
    }
}
