<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use DateTime;
use stdClass;

class ProgrammeChildrenProgrammeMapper extends AbstractProgrammeMapper
{
    public function getApsObject($programme): stdClass
    {
        $this->assertIsProgramme($programme);

        $output = [
            'type' => $this->getProgrammeType($programme),
            'pid' => (string) $programme->getPid(),
            'media_type' => $this->getMediaType($programme),
            'title' => $programme->getTitle(),
            'short_synopsis' => $programme->getShortSynopsis(),
            'image' => $this->getImageObject($programme->getImage()),
            'position' => $programme->getPosition(),
            'expected_child_count' => ($programme instanceof ProgrammeContainer) ? $programme->getExpectedChildCount() : null,
            'first_broadcast_date' => $this->getFirstBroadcastDate($programme),
            'has_medium_or_long_synopsis' => true, // This isn't actually used anywhere
            'has_related_links' => $programme->getRelatedLinksCount() > 0,
            'has_clips' => ($programme instanceof ProgrammeContainer || $programme instanceof Episode) ? $programme->getAvailableClipsCount() > 0 : false,
        ];

        // If Image is null then remove it from the feed
        if (is_null($output['image'])) {
            unset($output['image']);
        }

        if ($programme instanceof ProgrammeItem) {
            $output['has_segment_events'] = false; // This isn't used any more

            if ($programme->isStreamable()) {
                if ($programme->getStreamableUntil()) {
                    $output['available_until'] = $programme->getStreamableUntil() ? $programme->getStreamableUntil()->format(DateTime::ISO8601) : null;
                }

                $output['actual_start'] = $programme->getStreamableFrom() ? $programme->getStreamableFrom()->format(DateTime::ISO8601) : null;
            }

            $output['is_available_mediaset_pc_sd'] = $programme->isStreamable();
            $output['is_legacy_media'] = false; // This isn't actually used anywhere
        }

        return (object) $output;
    }
}
