<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use DateTime;
use InvalidArgumentException;
use stdClass;

class ProgrammeChildrenProgrammeMapper implements MapperInterface
{
    public function getApsObject($programme): stdClass
    {
        if (!($programme instanceof Programme)) {
            throw new InvalidArgumentException(sprintf(
                'Entity should be an instance of "%s". Got "%s"',
                'BBC\\ProgrammesPagesService\\Domain\\Entity\\Programme',
                get_class($programme)
            ));
        }

        $output = [
            'type' => $this->getType($programme),
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

    private function getType($entity): string
    {
        if ($entity instanceof Brand) {
            return 'brand';
        }
        if ($entity instanceof Series) {
            return 'series';
        }
        if ($entity instanceof Episode) {
            return 'episode';
        }
        if ($entity instanceof Clip) {
            return 'clip';
        }

        throw new InvalidArgumentException('Could not find type for entity "' . get_class($entity) . '"');
    }

    private function getMediaType(Programme $programme)
    {
        if (!($programme instanceof ProgrammeItem)) {
            return null;
        }

        $mediaType = $programme->getMediaType();
        return $mediaType != MediaTypeEnum::UNKNOWN ? $mediaType : null;
    }

    private function getFirstBroadcastDate(Programme $programme)
    {
        // Previously first_broadcast_date was based upon broadcasts of
        // episodes. As that info is expensive to calculate and we don't need it
        // elsewhere we're now going to base this off ReleaseDate which should
        // be good enough
        if (!($programme instanceof ProgrammeItem)) {
            return null;
        }

        return DateTime::createFromFormat(DateTime::ISO8601, $programme->getReleaseDate() . 'T12:00:00Z');
    }

    private function getImageObject(Image $image)
    {
        return (object) [
            'pid' => $image->getPid(),
        ];
    }
}
