<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\FindByPidSegmentEventMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FindByPidSegmentEventMapperTest extends TestCase
{
    public function testMappingSegmentEvent()
    {
        $segmentEvent = new SegmentEvent(
            new Pid('p00n28dd'),
            new Version(
                0,
                new Pid('b0193zsv'),
                new Episode(
                    [1],
                    new Pid('b0193zvj'),
                    'programme title',
                    'search title',
                    new Synopses('programme short', 'programme medium', 'programme long'),
                    new Image(new Pid('pmg00000'), 'image title', 'image short', 'image longest', 'image type', 'ext'),
                    2,
                    3,
                    true,
                    true,
                    true,
                    22,
                    'audio',
                    0,
                    0,
                    1,
                    0
                ),
                true,
                true,
                0,
                22,
                4
            ),
            new Segment(
                5,
                new Pid('p00fysyf'),
                'speech',
                new Synopses(
                    'segment short',
                    'segment medium',
                    'segment long'
                ),
                22,
                'segment title',
                8,
                []
            ),
            new Synopses('segment event short', 'segment event medium', 'segment event long'),
            'segment event title',
            true,
            6,
            7
        );

        $expectedSegmentEvent = (object) [
            'is_chapter' => true,
            'pid' => 'p00n28dd',
            'title' => 'segment event title',
            'short_synopsis' => 'segment event short',
            'medium_synopsis' => 'segment event medium',
            'long_synopsis' => 'segment event long',
            'position' => 7,
            'version_offset' => 6,
            'segment' => (object) [
                'pid' => 'p00fysyf',
                'type' => 'SpeechSegment',
                'duration' => 8,
                'title' => 'segment title',
                'short_synopsis' => 'segment short',
                'medium_synopsis' => 'segment medium',
                'long_synopsis' => 'segment long',
                'segment_events' => [],
                'track_title' => 'segment title',
                'contributions' => [],
                'release_title' => null,
                'catalogue_number' => null,
                'record_label' => null,
                'publisher' => null,
                'track_number' => null,
                'has_snippet' => false,
                'isrc' => null,
            ],
            'version' => (object) [
                'pid' => 'b0193zsv',
                'duration' => 4,
                'parent' => (object) [
                    'programme' => (object) [
                        'type' => 'episode',
                        'pid' => 'b0193zvj',
                        'title' => 'programme title',
                        'media_type' => 'audio',
                        'short_synopsis' => 'programme short',
                    ],
                ],
                'ownership' => (object) [],
            ],
        ];


        $mapper = new FindByPidSegmentEventMapper();
        $apsObject = $mapper->getApsObject($segmentEvent);

        $this->assertObjectNotHasAttribute('primary_contributor', $apsObject->{'segment'});
        $this->assertEquals($expectedSegmentEvent, $apsObject);
    }

    public function testMappingSegmentType()
    {
        $mappedSegmentEvent = $this->setupSegmentEventWithType('highlight');
        $this->assertEquals($mappedSegmentEvent->segment->type, 'Segment');

        $mappedSegmentEvent = $this->setupSegmentEventWithType('other');
        $this->assertEquals($mappedSegmentEvent->segment->type, 'Segment');

        $mappedSegmentEvent = $this->setupSegmentEventWithType('chapter');
        $this->assertEquals($mappedSegmentEvent->segment->type, 'SpeechSegment');

        $mappedSegmentEvent = $this->setupSegmentEventWithType('music');
        $this->assertEquals($mappedSegmentEvent->segment->type, 'MusicSegment');

        $mappedSegmentEvent = $this->setupSegmentEventWithType('classical');
        $this->assertEquals($mappedSegmentEvent->segment->type, 'ClassicalSegment');

        $mappedSegmentEvent = $this->setupSegmentEventWithType('speech');
        $this->assertEquals($mappedSegmentEvent->segment->type, 'SpeechSegment');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidDomainObject()
    {
        $image = new Image(new Pid('p01m5mss'), 'Title', 'ShortSynopsis', 'ShortSynopsis', 'standard', 'jpg');

        $mapper = new FindByPidSegmentEventMapper();
        $mapper->getApsObject($image);
    }

    private function setupSegmentEventWithType(string $type)
    {
        $programme = $this->createMock(Episode::class);
        $version = $this->createMock(Version::class);
        $segment = $this->createMock(Segment::class);
        $segmentEvent = $this->createMock(SegmentEvent::class);

        $segmentEvent->method('getSegment')->willReturn($segment);
        $segmentEvent->method('getVersion')->willReturn($version);
        $version->method('getProgrammeItem')->willReturn($programme);

        $segment->method('getType')->willReturn($type);
        $mapper = new FindByPidSegmentEventMapper();
        return $mapper->getApsObject($segmentEvent);
    }
}
