<?php

namespace Tests\BBC\CliftonBundle\TleosSliceByCategoryMapper;

use BBC\CliftonBundle\ApsMapper\TleosSliceByCategoryMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use PHPUnit_Framework_TestCase;

/**
 * @Cover BBC\CliftonBundle\ApsMapper\TleosSliceByCategoryMapper
 */
class TleosSliceByCategoryMapperTest extends PHPUnit_Framework_TestCase
{
    public function testMappingTleoSlice()
    {
        $slice = 'all';
        $programmes = $this->getCraftProgrammes();
        $category = $this->getCraftedCategory();
        $subCategories =  null;

        $expectedOutput = (object) [
            'category_slice' => [
                'slice' => $slice,
                'category' => (object) [
                    'type' => 'genre',
                    'id' => $category->getId(),
                    'key' => $category->getUrlKey(),
                    'title' => $category->getTitle(),
                    'broader' => (object) [],
                    'has_topic_page' => false,
                    'sameAs' => null,

                ],
                'programmes' => [
                    (object) [
                        'type' => 'series',
                        'pid' => $programmes->getPid(),
                        'title' => $programmes->getTitle(),
                        'image' => [
                                'pid' => $programmes->getImage()->getPid(),
                        ],
                        'is_available' => $programmes->isStreamable(),
                    ],
                ],
            ],
        ];

        $mapper = new TleosSliceByCategoryMapper();
        $output = $mapper->getApsObject(
            [$programmes],
            $category,
            $slice,
            $subCategories
        );

        $this->assertEquals($expectedOutput, $output);
    }

    private function getCraftProgrammes()
    {
        $series = new Series(
            [1],
            new Pid('b06hgxtt'),
            'Series 9 - Omnibus',
            'Search Title',
            new Synopses('Short Synopsis', '', ''),
            new Image(new Pid('p01m5mss'), 'Title', 'ShortSynopsis', 'ShortSynopsis', 'standard', 'jpg'),
            1101,
            1102,
            false,
            true, // $isStreamable
            false,
            1103,
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

        return $series;
    }

    private function getCraftedCategory()
    {
        $dbAncestryIds = [0];
        return new Genre($dbAncestryIds, 'anyid', 'anytitle', 'anyurlkey');
    }
}
