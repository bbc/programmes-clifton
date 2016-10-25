<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\ChildrenSeriesOfContainerMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use DateTimeImmutable;
use PHPUnit_Framework_TestCase;
use InvalidArgumentException;

class ChildrenSeriesOfContainerMapperTest extends PHPUnit_Framework_TestCase
{
    public function testMappingChildrenSeriesOfContainer()
    {
        $series = new Series(
            [1],
            new Pid('p0000000'),
            'Series 1',
            'Search Title 1',
            new Synopses('Short Synopsis', 'Medium Synopsis', 'Long Synopsis'),
            new Image(new Pid('p0000001'), 'Title', 'ShortSynopsis', 'LongestSynopsis', 'standard', 'jpg'),
            0,
            1,
            false,
            false,
            false,
            2,
            3,
            4,
            5,
            6,
            false,
            null,
            7,
            null,
            [],
            [],
            new DateTimeImmutable('2000-01-01 00:00:00'),
            8
        );

        $expectedResult = (object) [
            'type' => 'series',
            'pid' => 'p0000000',
            'title' => 'Series 1',
            'short_synopsis' => 'Short Synopsis',
            'image' => (object) [
                'pid' => 'p0000001',
            ],
            'position' => 7,
            'expected_child_count' => 8,
            'first_broadcast_date' => '2000-01-01T00:00:00Z',
        ];

        $mapper = new ChildrenSeriesOfContainerMapper();
        $apsObject = $mapper->getApsObject($series);

        $this->assertEquals($apsObject, $expectedResult);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidDomainObject()
    {
        $image = new Image(new Pid('p01m5mss'), 'Title', 'ShortSynopsis', 'ShortSynopsis', 'standard', 'jpg');

        $mapper = new ChildrenSeriesOfContainerMapper();
        $mapper->getApsObject($image);
    }
}
