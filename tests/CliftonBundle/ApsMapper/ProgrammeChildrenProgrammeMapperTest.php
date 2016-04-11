<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Enumeration\IsPodcastableEnum;
use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\PartialDate;
use BBC\CliftonBundle\ApsMapper\ProgrammeChildrenProgrammeMapper;
use DateTime;
use DateTimeImmutable;
use PHPUnit_Framework_TestCase;

class ProgrammeChildrenProgrammeMapperTest extends PHPUnit_Framework_TestCase
{
    public function testMappingSeries()
    {
        $series = new Series(
            new Pid('b06hgxtt'),
            'Series 9 - Omnibus',
            'Search Title',
            'Short Synopsis',
            'Long Synopsis',
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
            IsPodcastableEnum::NO,
            null,
            new PartialDate('2015-01-02'),
            101,
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
            'first_broadcast_date' => null,
            'has_medium_or_long_synopsis' => true,
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
            new Pid('b06tl32t'),
            'The Husbands of River Song',
            'Search Title',
            'Short Synopsis',
            'Long Synopsis',
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
            new PartialDate(2015, 02, 00),
            101,
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
            'first_broadcast_date' => '2015-02-01T12:00:00Z',
            'has_medium_or_long_synopsis' => true,
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

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidDomainObject()
    {
        $image = new Image(new Pid('p01m5mss'), 'Title', 'ShortSynopsis', 'ShortSynopsis', 'standard', 'jpg');

        $mapper = new ProgrammeChildrenProgrammeMapper();
        $mapper->getApsObject($image);
    }
}
