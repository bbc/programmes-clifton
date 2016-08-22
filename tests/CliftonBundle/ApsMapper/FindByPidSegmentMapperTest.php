<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\FindByPidSegmentMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\MusicSegment;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

class FindByPidSegmentMapperTest extends PHPUnit_Framework_TestCase
{
    public function testMappingSegment()
    {
        $segment = new Segment(
            0,
            new Pid('p00gp1d3'),
            'chapter',
            'Female conductors',
            new Synopses(
                'short',
                'medium',
                'long'
            ),
            1380
        );

        $expectedSegment = (object) [
            'pid' => 'p00gp1d3',
            'type' => 'chapter',
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
        $segment = new MusicSegment(
            2688563,
            new Pid('p03g4kqr'),
            'classical',
            'Battle of Britain; March introduction – excerpt',
            new Synopses('', '', ''),
            167,
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
            'contributions' => [],
            'release_title' => 'Dornik',
            'catalogue_number' => 'CHAN10361',
            'record_label' => 'Chandos',
            'publisher' => 'Sony Music Entertainment',
            'track_number' => 18,
            'has_snippet' => false,
            'isrc' => null,
        ];

        $mapper = new FindByPidSegmentMapper();
        $this->assertEquals($expectedSegment, $mapper->getApsObject($segment));
    }

    public function testMappingNumericTitleResultsInNumericData()
    {
        // This is a dumb bug in APS, but we want to mimic it's behaviour
        // If the Title is a numeric string, then APS outputs the value as a
        // number, rather than a string
        // e.g. http://open.live.bbc.co.uk/aps/programmes/p002d9g3.json
        $segment = $this->createMock(Segment::CLASS);
        $segment->method('getTitle')->willReturn('1989');

        $mapper = new FindByPidSegmentMapper();
        $apsObject = $mapper->getApsObject($segment);

        $this->assertSame(1989.0, $apsObject->title);
        $this->assertSame(1989.0, $apsObject->{'track_title'});
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
}
