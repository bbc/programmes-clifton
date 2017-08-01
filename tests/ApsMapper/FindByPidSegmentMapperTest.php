<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\FindByPidSegmentMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\Entity\Contributor;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\MusicSegment;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\Unfetched\UnfetchedProgramme;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FindByPidSegmentMapperTest extends TestCase
{
    public function testMappingSegment()
    {
        $segment = new Segment(
            0,
            new Pid('p00gp1d3'),
            'chapter',
            new Synopses(
                'short',
                'medium',
                'long'
            ),
            22,
            'Female conductors',
            1380,
            []
        );

        $expectedSegment = (object) [
            'pid' => 'p00gp1d3',
            'type' => 'speech',
            'duration' => 1380,
            'title' => 'Female conductors',
            'short_synopsis' => 'short',
            'medium_synopsis' => 'medium',
            'long_synopsis' => 'long',
            'segment_events' => [],
            'track_title' => 'Female conductors',
            'contributions' => [],
            'release_title' => null,
            'catalogue_number' => null,
            'record_label' => null,
            'publisher' => null,
            'track_number' => null,
            'has_snippet' => false,
            'isrc' => null,
        ];

        $mapper = new FindByPidSegmentMapper();
        $this->assertEquals($expectedSegment, $mapper->getApsObject($segment));
    }

    public function testMappingMusicSegment()
    {
        $contributions = [
            new Contribution(
                new Pid('c0000001'),
                new Contributor(1, new Pid('cp0000001'), 'Performer', 'Name'),
                new UnfetchedProgramme(),
                'Performer'
            ),
        ];

        $segment = new MusicSegment(
            2688563,
            new Pid('p03g4kqr'),
            'classical',
            new Synopses('', '', ''),
            22,
            'Battle of Britain; March introduction – excerpt',
            167,
            $contributions,
            'n3hxrj',
            'Dornik',
            'CHAN10361',
            'Chandos',
            'Sony Music Entertainment',
            '18',
            '1',
            'The Film Music of Dmitri Shostakovich, Vol. 3',
            'C',
            null
        );

        $expectedSegment = (object) [
            'pid' => 'p03g4kqr',
            'type' => 'classical',
            'duration' => 167,
            'title' => 'Battle of Britain; March introduction – excerpt',
            'short_synopsis' => '',
            'medium_synopsis' => '',
            'long_synopsis' => '',
            'segment_events' => [],
            'track_title' => 'Battle of Britain; March introduction – excerpt',
            'contributions' => [
                (object) [
                    'pid' => 'cp0000001',
                    'name' => 'Name',
                    'role' => 'Performer',
                    'musicbrainz_gid' => null,
                ],
            ],
            'release_title' => 'Dornik',
            'catalogue_number' => 'CHAN10361',
            'record_label' => 'Chandos',
            'publisher' => 'Sony Music Entertainment',
            'track_number' => '18',
            'has_snippet' => false,
            'isrc' => null,
            'primary_contributor' => (object) [
                'pid' => 'cp0000001',
                'name' => 'Name',
                'sort_name' => null,
                'musicbrainz_gid' => null,
            ],
        ];

        $mapper = new FindByPidSegmentMapper();
        $this->assertEquals($expectedSegment, $mapper->getApsObject($segment));
    }

    public function testMappingSegmentType()
    {
        //APS only knows about the types 'classical', 'music', 'speech' (and 'deleted', but we don't use that).
        //Therefore, we have to map the values to the ones APS knows. If APS doesn't recognize the type, it outputs
        //an empty string

        //Stuff APS doesn't know about
        $mappedSegment = $this->setupSegmentWithType('highlight');
        $this->assertEquals($mappedSegment->type, '');

        $mappedSegment = $this->setupSegmentWithType('other');
        $this->assertEquals($mappedSegment->type, '');

        //Stuff APS knows about
        $mappedSegment = $this->setupSegmentWithType('chapter');
        $this->assertEquals($mappedSegment->type, 'speech');

        $mappedSegment = $this->setupSegmentWithType('music');
        $this->assertEquals($mappedSegment->type, 'music');

        $mappedSegment = $this->setupSegmentWithType('classical');
        $this->assertEquals($mappedSegment->type, 'classical');

        $mappedSegment = $this->setupSegmentWithType('speech');
        $this->assertEquals($mappedSegment->type, 'speech');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidDomainObject()
    {
        $image = new Image(new Pid('p01m5mss'), 'Title', 'ShortSynopsis', 'ShortSynopsis', 'standard', 'jpg');

        $mapper = new FindByPidSegmentMapper();
        $mapper->getApsObject($image);
    }

    private function setupSegmentWithType(string $type)
    {
        $segment = $this->createMock(Segment::class);
        $segment->method('getType')->willReturn($type);
        $mapper = new FindByPidSegmentMapper();
        return $mapper->getApsObject($segment);
    }
}
