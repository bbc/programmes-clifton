<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\AtoZItemMapper;
use BBC\ProgrammesPagesService\Domain\Entity\AtoZTitle;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\MasterBrand;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\ValueObject\Mid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Nid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use PHPUnit_Framework_TestCase;

class AtoZItemMapperTest extends PHPUnit_Framework_TestCase
{
    public function testMappingAtoZ()
    {
        $mbImage = new Image(new Pid('p01m5msq'), 'Title', 'ShortSynopsis', 'ShortSynopsis', 'standard', 'jpg');
        $brand = new Brand(
            [1],
            new Pid('b006q2x0'),
            'Doctor Who',
            'Search Title',
            new Synopses('Short Synopsis', 'Medium Synopsis', ' '),
            new Image(new Pid('p01m5mss'), 'Title', 'ShortSynopsis', 'ShortSynopsis', 'standard', 'jpg'),
            1101,
            1102,
            false,
            false,
            false,
            1103,
            1201,
            1202,
            1203,
            1204,
            1205,
            false,
            null, // Parent
            3,
            new MasterBrand(
                new Mid('bbc_one'),
                'BBC One',
                $mbImage,
                new Network(new Nid('bbc_one'), 'BBC One', $mbImage, 'bbcone', 'tv', 'tv')
            ),
            [],
            [],
            new \DateTimeImmutable('2000-01-01 00:00:00'),
            10
        );

        $atoztitle = new AtoZTitle('Doctor Who', 'd', $brand);

        $expectedOutput = (object) [
            'title' => 'Doctor Who',
            'letter' => 'd',
            'programme' => (object) [
                'type' => 'brand',
                'pid' => 'b006q2x0',
                'title' => 'Doctor Who',
                'short_synopsis' => 'Short Synopsis',
                'image' => (object) ['pid' => 'p01m5mss'],
                'position' => 3,
                'expected_child_count' => 10,
                'first_broadcast_date' => '2000-01-01T00:00:00Z',
                'is_available' => false,
                'ownership' => (object) [
                    'service' => (object) [
                        'type' => 'tv',
                        'id' => 'bbc_one',
                        'key' => 'bbcone',
                        'title' => 'BBC One',
                    ],
                ],
            ],
        ];

        $mapper = new AtoZItemMapper();

        $output = $mapper->getApsObject($atoztitle);

        $this->assertEquals($expectedOutput, $output);
    }
}
