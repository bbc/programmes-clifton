<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\PartialDate;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use BBC\CliftonBundle\ApsMapper\ProgrammeChildrenProgrammeMapper;
use DateTime;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

class ProgrammeChildrenProgrammeMapperTest extends PHPUnit_Framework_TestCase
{
    public function testMappingSeries()
    {
        $series = new Series(
            1,
            new Pid('b06hgxtt'),
            'Series 9 - Omnibus',
            'Search Title',
            new Synopses('Short Synopsis', '', ''),
            new Image(new Pid('p01m5mss'), 'Title', 'ShortSynopsis', 'ShortSynopsis', 'standard', 'jpg'),
            1101,
            1102,
            false,
            false,
            1201,
            1202,
            1203,
            1204,
            1205,
            false,
            null,
            2101,
            null,
            [],
            [],
            new \DateTimeImmutable('2000-01-01 00:00:00'),
            2201
        );

        $expectedOutput = (object) [
            'type' => 'series',
            'pid' => 'b06hgxtt',
            'title' => 'Series 9 - Omnibus',
            'media_type' => null,
            'short_synopsis' => 'Short Synopsis',
            'image' => (object) ['pid' => 'p01m5mss'],
            'position' => 2101,
            'expected_child_count' => 2201,
            'first_broadcast_date' => '2000-01-01T00:00:00Z',
            'has_medium_or_long_synopsis' => false,
            'has_related_links' => true,
            'has_clips' => true,
        ];

        $mapper = new ProgrammeChildrenProgrammeMapper();
        $this->assertEquals($expectedOutput, $mapper->getApsObject($series));
    }

    public function testMappingEpisode()
    {
        $streamableFrom = new DateTimeImmutable('2000-01-02T00:00:00Z');
        $streamableUntil = new DateTimeImmutable('2000-01-03T00:00:00Z');

        $series = new Episode(
            1,
            new Pid('b06tl32t'),
            'The Husbands of River Song',
            'Search Title',
            new Synopses('Short Synopsis', '', ''),
            new Image(new Pid('p01m5mss'), 'Title', 'ShortSynopsis', 'ShortSynopsis', 'standard', 'jpg'),
            1101,
            1102,
            true,
            true,
            MediaTypeEnum::VIDEO,
            0,
            1301,
            1302,
            1303,
            null,
            2101,
            null,
            [],
            [],
            new DateTimeImmutable('2000-01-01 00:00:00Z'),
            new PartialDate(2015, 02, 00),
            1001,
            $streamableFrom,
            $streamableUntil
        );

        $expectedOutput = (object) [
            'type' => 'episode',
            'pid' => 'b06tl32t',
            'title' => 'The Husbands of River Song',
            'media_type' => 'audio_video',
            'short_synopsis' => 'Short Synopsis',
            'image' => (object) ['pid' => 'p01m5mss'],
            'position' => 2101,
            'expected_child_count' => null,
            'first_broadcast_date' => '2000-01-01T00:00:00Z',
            'has_medium_or_long_synopsis' => false,
            'has_related_links' => true,
            'has_clips' => true,
            'has_segment_events' => false,
            'available_until' => '2000-01-03T00:00:00Z',
            'actual_start' => '2000-01-02T00:00:00Z',
            'is_available_mediaset_pc_sd' => true,
            'is_legacy_media' => false,
        ];

        $mapper = new ProgrammeChildrenProgrammeMapper();
        $this->assertEquals($expectedOutput, $mapper->getApsObject($series));
    }

    public function testMappingDefaultImageResultsInAbsentImageField()
    {
        $image = $this->createMock(Image::CLASS);
        $image->method('getPid')->willReturn(new Pid('p01tqv8z'));

        $series = $this->createMock(Series::CLASS);
        $series->method('getImage')->willReturn($image);

        $mapper = new ProgrammeChildrenProgrammeMapper();
        $apsObject = $mapper->getApsObject($series);

        $this->assertObjectNotHasAttribute('image', $apsObject);
    }

    public function testMappingNumericTitleResultsInNumericData()
    {
        // This is a dumb bug in APS, but we want to mimic it's behaviour
        // If the Title is a numeric string, then APS outputs the value as a
        // number, rather than a string
        // e.g. http://open.live.bbc.co.uk/aps/programmes/b008hskr.json
        $series = $this->createMock(Series::CLASS);
        $series->method('getTitle')->willReturn('2008');


        $mapper = new ProgrammeChildrenProgrammeMapper();
        $apsObject = $mapper->getApsObject($series);

        $this->assertSame(2008, $apsObject->title);
    }

    /**
     * @dataProvider mappingHasMediumOrLongSynopsisDataProvider
     */
    public function testMappingHasMediumOrLongSynopsis($synopses, $expectedValue)
    {
        $series = $this->createMock(Series::CLASS);
        $series->method('getSynopses')->willReturn($synopses);

        $mapper = new ProgrammeChildrenProgrammeMapper();
        $apsObject = $mapper->getApsObject($series);

        $this->assertSame($expectedValue, $apsObject->{'has_medium_or_long_synopsis'});
    }

    public function mappingHasMediumOrLongSynopsisDataProvider()
    {
        return [
            [new Synopses('Short', '', ''), false],
            [new Synopses('Short', 'Medium', ''), true],
            [new Synopses('Short', '', 'Long'), true],
            [new Synopses('Short', 'Medium', 'Long'), true],
        ];
    }

    public function testMappingHasSegments()
    {
        $episode = $this->createMock(Episode::CLASS);
        $episode->method('getSegmentEventCount')->willReturn(1);

        $mapper = new ProgrammeChildrenProgrammeMapper();
        $apsObject = $mapper->getApsObject($episode);

        $this->assertSame(true, $apsObject->{'has_segment_events'});
        $this->assertSame('Featured items', $apsObject->{'segments_title'});
    }

    public function testMappingDatesGMT()
    {
        $episode = $this->createMock(Episode::CLASS);
        $episode->method('isStreamable')->willReturn(true);
        $episode->method('getFirstBroadcastDate')->willReturn(new DateTimeImmutable('1999-02-15T21:30:00Z'));
        $episode->method('getStreamableUntil')->willReturn(new DateTimeImmutable('1999-02-15T21:30:00Z'));
        $episode->method('getStreamableFrom')->willReturn(new DateTimeImmutable('1999-02-15T21:30:00Z'));

        $mapper = new ProgrammeChildrenProgrammeMapper();
        $apsObject = $mapper->getApsObject($episode);

        $this->assertEquals('1999-02-15T21:30:00Z', $apsObject->{'first_broadcast_date'});
        $this->assertEquals('1999-02-15T21:30:00Z', $apsObject->{'available_until'});
        $this->assertEquals('1999-02-15T21:30:00Z', $apsObject->{'actual_start'});
    }

    public function testMappingDatesBST()
    {
        $episode = $this->createMock(Episode::CLASS);
        $episode->method('isStreamable')->willReturn(true);
        $episode->method('getFirstBroadcastDate')->willReturn(new DateTimeImmutable('2007-05-18T22:55:00+01:00'));
        $episode->method('getStreamableUntil')->willReturn(new DateTimeImmutable('2007-05-18T22:55:00+01:00'));
        $episode->method('getStreamableFrom')->willReturn(new DateTimeImmutable('2007-05-18T22:55:00+01:00'));

        $mapper = new ProgrammeChildrenProgrammeMapper();
        $apsObject = $mapper->getApsObject($episode);

        $this->assertEquals('2007-05-18T22:55:00+01:00', $apsObject->{'first_broadcast_date'});
        $this->assertEquals('2007-05-18T22:55:00+01:00', $apsObject->{'available_until'});
        $this->assertEquals('2007-05-18T22:55:00+01:00', $apsObject->{'actual_start'});
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidDomainObject()
    {
        $image = new Image(new Pid('p01m5mss'), 'Title', 'ShortSynopsis', 'ShortSynopsis', 'standard', 'jpg');

        $mapper = new ProgrammeChildrenProgrammeMapper();
        $mapper->getApsObject($image);
    }
}
