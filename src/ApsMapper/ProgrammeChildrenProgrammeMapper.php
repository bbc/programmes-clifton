<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use stdClass;

class ProgrammeChildrenProgrammeMapper implements MapperInterface
{
    use Traits\ProgrammeUtilitiesTrait;

    public function getApsObject($programme): stdClass
    {
        $this->assertIsProgramme($programme);

        $output = [
            'type' => $this->getProgrammeType($programme),
            'pid' => (string) $programme->getPid(),
            'media_type' => $this->getMediaType($programme),
            'title' => $this->getProgrammeTitle($programme->getTitle()),
            'short_synopsis' => $programme->getShortSynopsis(),
            'image' => $this->getImageObject($programme->getImage()),
            'position' => $programme->getPosition(),
            'expected_child_count' => ($programme instanceof ProgrammeContainer) ? $programme->getExpectedChildCount() : null,
            'first_broadcast_date' => $this->getFirstBroadcastDate($programme),
            'has_medium_or_long_synopsis' => (!empty($programme->getSynopses()->getMediumSynopsis()) || !empty($programme->getSynopses()->getLongSynopsis())),
            'has_related_links' => $programme->getRelatedLinksCount() > 0,
            'has_clips' => ($programme instanceof ProgrammeContainer || $programme instanceof Episode) ? $programme->getAvailableClipsCount() > 0 : false,
        ];

        // If Image is null then remove it from the feed
        if (is_null($output['image'])) {
            unset($output['image']);
        }

        if ($programme instanceof ProgrammeItem) {
            $hasSegmentEvents = !!$programme->getSegmentEventCount();
            $output['has_segment_events'] = $hasSegmentEvents;

            if ($hasSegmentEvents) {
                $output['segments_title'] = 'Featured items';
            }

            if ($programme->isStreamable()) {
                if ($programme->getStreamableUntil()) {
                    $output['available_until'] = $programme->getStreamableUntil() ? $this->formatDateTime($programme->getStreamableUntil()) : null;
                }

                $output['actual_start'] = $programme->getStreamableFrom() ? $this->formatDateTime($programme->getStreamableFrom()) : null;
            }

            $output['is_available_mediaset_pc_sd'] = $programme->isStreamable();
            $output['is_legacy_media'] = false; // This isn't actually used anywhere
        }

        return (object) $output;
    }
}
