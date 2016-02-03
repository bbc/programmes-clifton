<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Enumeration\IsPodcastableEnum;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\PartialDate;
use BBC\CliftonBundle\ApsMapper\ProgrammeChildrenProgrammeMapper;
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
            new Image(new Pid('p01m5mss'), 'Title', 'ShortSynopsis', 'standard', 'jpg'),
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
            'has_medium_or_long_synopsis' => true,
            'has_related_links' => true,
            'has_clips' => true,
        ];

        $mapper = new ProgrammeChildrenProgrammeMapper();
        $this->assertEquals($expectedOutput, $mapper->getApsObject($series));
    }
}
