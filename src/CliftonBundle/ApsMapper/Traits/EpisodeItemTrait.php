<?php

namespace BBC\CliftonBundle\ApsMapper\Traits;

use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use DateTimeImmutable;

trait EpisodeItemTrait
{
    use ProgrammeUtilitiesTrait;
    use SegmentUtilitiesTrait;

    public function mapEpisodeItem(Episode $episode, bool $extraMetadataForCategories = false)
    {
        $output = [
            'type' => $this->getProgrammeType($episode),
            'pid' => (string) $episode->getPid(),
            'position' => $episode->getPosition(),
            'title' => $episode->getTitle(),
            'short_synopsis' => $episode->getShortSynopsis(),
            'media_type' => $episode->getMediaType() ? $episode->getMediaType() : null,
            'duration' => $episode->getDuration(),
        ];

        if (!is_null($this->getImageObject($episode->getImage()))) {
            $output['image'] = $this->getImageObject($episode->getImage());
        }

        $output['display_titles'] = $this->getDisplayTitle($episode);
        $output['first_broadcast_date'] = $episode->getFirstBroadcastDate() ? $this->formatDateTime($episode->getFirstBroadcastDate()) : null;

        if ($extraMetadataForCategories) {
            $output['has_medium_or_long_synopsis'] =
                ($episode->getSynopses()->getMediumSynopsis() || $episode->getSynopses()->getLongSynopsis());
            $output['has_related_links'] = ($episode->getRelatedLinksCount() ? true : false);
            $output['has_clips'] = ($episode->getAvailableClipsCount() ? true : false);
            $output['has_segment_events'] = ($episode->getSegmentEventCount() ? true : false);

            if ($episode->getSegmentEventCount()) {
                $output['segments_title'] = '';
            }
        }

        if (!is_null($this->mapSegmentOwnership($episode))) {
            $output['ownership'] = $this->mapSegmentOwnership($episode);
        }

        if ($episode->getParent()) {
            $output['programme'] = $this->mapEpisodeItemParent($episode->getParent());
        }

        if ($episode->isStreamable()) {
            if ($episode->getStreamableUntil()) {
                $output['available_until'] = $episode->getStreamableUntil() ?
                    $this->formatDateTime($episode->getStreamableUntil()) :
                    null;
            }

            $output['actual_start'] = $episode->getStreamableFrom() ?
                $this->formatDateTime($episode->getStreamableFrom()) :
                null;
        }

        $output['is_available_mediaset_pc_sd'] = $episode->isStreamable();
        $output['is_legacy_media'] = false; // This isn't actually used anywhere

        if ($episode->isStreamable()) {
            $output['media'] = $this->mapEpisodeItemMedia($episode);
        }

        return (object) $output;
    }

    private function mapEpisodeItemParent(Programme $programme)
    {
        $output = [
            'type' => $this->getProgrammeType($programme),
            'pid' => (string) $programme->getPid(),
            'title' => $programme->getTitle(),
            'position' => $programme->getPosition(),
        ];

        if (!is_null($this->getImageObject($programme->getImage()))) {
            $output['image'] = $this->getImageObject($programme->getImage());
        }

        $output['expected_child_count'] = ($programme instanceof ProgrammeContainer) ?
            $programme->getExpectedChildCount() :
            null;
        $output['first_broadcast_date'] = $this->getFirstBroadcastDate($programme);

        if (!is_null($this->mapSegmentOwnership($programme))) {
            $output['ownership'] = $this->mapSegmentOwnership($programme);
        }

        if (!is_null($programme->getParent())) {
            $output['programme'] = $this->mapEpisodeItemParent($programme->getParent());
        }

        return (object) $output;
    }

    private function mapEpisodeItemMedia(ProgrammeItem $programme)
    {
        $output = [];
        $output['format'] = $this->getMediaType($programme) === 'audio' ? 'audio' : 'video';

        $action = $this->getMediaType($programme) === 'audio' ? 'listen' : 'watch';

        $output['expires'] = $programme->getStreamableUntil() ? $this->formatDateTime($programme->getStreamableUntil()) : null;

        $output['availability'] = 'Available to ' . $action;

        if (!is_null($programme->getStreamableUntil())) {
            $now = new DateTimeImmutable();
            $remainingSeconds = $programme->getStreamableUntil()->getTimestamp() - $now->getTimestamp();

            // If over 400 days, ignore time left, according to APS
            // https://repo.dev.bbc.co.uk/services/aps/trunk/lib/Models/Availability.pm
            if ($remainingSeconds <= 86400 * 400) {
                if ($remainingSeconds >= 86400 * 30) {
                    $remainingTime = round($remainingSeconds/(86400.0 * 30.0));
                    $unit = 'month';
                } elseif ($remainingSeconds >= 86400) {
                    $remainingTime = round($remainingSeconds/86400.0);
                    $unit = 'day';
                } elseif ($remainingSeconds >= 3600) {
                    $remainingTime = round($remainingSeconds/3600.0);
                    $unit = 'hour';
                } else {
                    $remainingTime = round($remainingSeconds/60.0);
                    $unit = 'minute';
                }

                if ($remainingTime > 1) {
                    $unit .= 's';
                }

                $output['availability'] = $remainingTime . ' ' . $unit . ' left to ' . $action;
            } // If over 400 days, remove 'expires' field
            else {
                unset($output['expires']);
            }
        }

        return (object) $output;
    }
}
