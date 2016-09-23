<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use InvalidArgumentException;
use stdClass;

class FindByPidSegmentEventMapper implements MapperInterface
{
    use Traits\ProgrammeUtilitiesTrait;
    use Traits\SegmentUtilitiesTrait;

    public function getApsObject($segmentEvent, array $contributions = [], array $segmentEventsBySegment = []): stdClass
    {
        /** @var SegmentEvent $segment */
        $this->assertIsSegmentEvent($segmentEvent);

         $output = [
            'is_chapter' => $segmentEvent->isChapter(),
            'pid' => (string) $segmentEvent->getPid(),
            'title' => $segmentEvent->getTitle(),
            'short_synopsis' => $segmentEvent->getSynopses()->getShortSynopsis(),
            'medium_synopsis' => $segmentEvent->getSynopses()->getMediumSynopsis(),
            'long_synopsis' => $segmentEvent->getSynopses()->getLongSynopsis(),
            'position' => $segmentEvent->getPosition(),
            'version_offset' => $segmentEvent->getOffset(),
            'segment' => $this->getSegment($segmentEvent->getSegment(), $contributions, $segmentEventsBySegment),
            'version' => $this->getVersion($segmentEvent->getVersion()),
         ];

         return (object) $output;
    }

    private function assertIsSegmentEvent($item)
    {
        if (!($item instanceof SegmentEvent)) {
            throw new InvalidArgumentException(sprintf(
                'Entity should be an instance of "%s". Got "%s"',
                'BBC\\ProgrammesPagesService\\Domain\\Entity\\SegmentEvent',
                (is_object($item) ? get_class($item) : gettype($item))
            ));
        }
    }

    private function getSegment(Segment $segment, array $contributions, array $segmentEventsBySegment)
    {
        $output = $this->mapSegment($segment, $contributions);
        $output['type'] = $this->getSegmentType($segment->getType());
        $output['segment_events'] = array_map([$this, 'getSegmentEvent'], $segmentEventsBySegment);

        return (object) $output;
    }

    private function getVersion(Version $version)
    {
        $output = [
            'pid' => (string) $version->getPid(),
            'duration' => $version->getDuration(),
            'parent' => $this->getParent($version->getProgrammeItem()),
        ];

        $ownership = $this->getSegmentOwnership($version->getProgrammeItem());
        $output['ownership'] = (object) [];

        if ($ownership) {
            $output['ownership'] = (object) ['ownership' => $ownership];
        }

        return (object) $output;
    }

    private function getParent(Programme $programme)
    {
        $output = [
            'type' => $this->getProgrammeType($programme),
            'pid' => (string) $programme->getPid(),
            'title' => $this->getProgrammeTitle($programme->getTitle()),
            'media_type' => $this->getMediaType($programme),
            'short_synopsis' => $programme->getShortSynopsis(),
        ];

        return (object) ['programme' => (object) $output];
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
        ];
        return (object) $output;
    }

    private function getSegmentType(string $type) : string
    {
        // This piece of code replicates this from tapp:
        // https://github.com/bbc/programmes-tapp/blob/master/lib/tapp/np_builder.rb#L326-L338
        // It shouldn't be like this, bu' it be. ¯\_(ツ)_/¯
        if ($type === 'classical') {
            return 'ClassicalSegment';
        }
        if ($type === 'music') {
            return 'MusicSegment';
        }
        if ($type === 'speech' || $type === 'chapter') {
            return 'SpeechSegment';
        }
        return 'Segment';
    }
}
