<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Enumeration\IsPodcastableEnum;
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
            0,
            1,
            false,
            false,
            0,
            0,
            11,
            0,
            0,
            false,
            null,
            101,
            null,
            [],
            [],
            new \DateTimeImmutable('1970-01-01 00:00:00'),
            1001
        );

        $expectedOutput = (object) [
            'type' => 'series',
            'pid' => 'b06hgxtt',
            'title' => 'Series 9 - Omnibus',
            'media_type' => null,
            'short_synopsis' => 'Short Synopsis',
            'image' => (object) ['pid' => 'p01m5mss'],
            'position' => 101,
            'expected_child_count' => 1001,
            'first_broadcast_date' => '1970-01-01T00:00:00Z',
            'has_medium_or_long_synopsis' => false,
            'has_related_links' => true,
            'has_clips' => true,
        ];

        $mapper = new ProgrammeChildrenProgrammeMapper();
        $this->assertEquals($expectedOutput, $mapper->getApsObject($series));
    }

    public function testMappingEpisode()
    {
        $streamableFrom = new DateTimeImmutable();
        $streamableUntil = new DateTimeImmutable();

        $series = new Episode(
            1,
            new Pid('b06tl32t'),
            'The Husbands of River Song',
            'Search Title',
            new Synopses('Short Synopsis', '', ''),
            new Image(new Pid('p01m5mss'), 'Title', 'ShortSynopsis', 'ShortSynopsis', 'standard', 'jpg'),
            1,
            2,
            true,
            true,
            MediaTypeEnum::VIDEO,
            11,
            12,
            13,
            null,
            101,
            null,
            [],
            [],
            new PartialDate(2015, 02, 00),
            new \DateTimeImmutable('1970-01-01 00:00:00'),
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
            'position' => 101,
            'expected_child_count' => null,
            'first_broadcast_date' => '1970-01-01T00:00:00Z',
            'has_medium_or_long_synopsis' => false,
            'has_related_links' => true,
            'has_clips' => true,
            'has_segment_events' => false,
            'available_until' => $streamableUntil->format(DateTime::ISO8601),
            'actual_start' => $streamableFrom->format(DateTime::ISO8601),
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
