<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use InvalidArgumentException;
use stdClass;

class FindByPidSegmentMapper implements MapperInterface
{
    use Traits\ProgrammeUtilitiesTrait;
    use Traits\SegmentUtilitiesTrait;

    public function getApsObject($segment, array $segmentEvents = []): stdClass
    {
        /** @var Segment $segment */
        $this->assertIsSegment($segment);

        $mappedSegmentEvents = array_map([$this, 'getSegmentEvent'], $segmentEvents);
        $output = $this->mapSegment($segment, $mappedSegmentEvents);

        return (object) $output;
    }

    private function assertIsSegment($item)
    {
        if (!($item instanceof Segment)) {
            throw new InvalidArgumentException(sprintf(
                'Entity should be an instance of "%s". Got "%s"',
                'BBC\\ProgrammesPagesService\\Domain\\Entity\\Segment',
                (is_object($item) ? get_class($item) : gettype($item))
            ));
        }
    }

    private function getSegmentEvent(SegmentEvent $segmentEvent)
    {
        $output = [
            'pid' => (string) $segmentEvent->getPid(),
            'title' => $segmentEvent->getTitle(),
            'short_synopsis' => $segmentEvent->getSynopses()->getShortSynopsis(),
            'medium_synopsis' => $segmentEvent->getSynopses()->getMediumSynopsis(),
            'long_synopsis' => $segmentEvent->getSynopses()->getLongSynopsis(),
            'version_offset' => $segmentEvent->getOffset(),
            'position' => $segmentEvent->getPosition(),
            'is_chapter' => $segmentEvent->isChapter(),
            'version' => $this->getVersion($segmentEvent->getVersion()),
        ];

        return (object) $output;
    }

    private function getVersion(Version $version)
    {
        $output = [
            'pid' => (string) $version->getPid(),
            'duration' => $version->getDuration(),
            'programme' => $this->getParent($version->getProgrammeItem()),
        ];

        return (object) $output;
    }

    private function getParent(Programme $programme)
    {
        $output = [
            'type' => $this->getProgrammeType($programme),
            'pid' => (string) $programme->getPid(),
            'title' => $this->getProgrammeTitle($programme->getTitle()),
            'image' => $this->getImageObject($programme->getImage()),
            'short_synopsis' => $programme->getShortSynopsis(),
            'media_type' => $this->getMediaType($programme),
        ];

        // If Image is null then remove it from the feed
        if (is_null($output['image'])) {
            unset($output['image']);
        }

        // Parents are only added to items with parents
        if ($programme->getParent()) {
            $output['parent'] = (object) ['programme' => $this->getParent($programme->getParent())];
        }

        if ($this->mapSegmentOwnership($programme)) {
            $output['ownership'] = $this->mapSegmentOwnership($programme);
        }

        return (object) $output;
    }
}
