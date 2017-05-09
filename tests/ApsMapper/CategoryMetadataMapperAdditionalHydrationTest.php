<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\CategoryMetadataMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CategoryMetadataMapperAdditionalHydrationTest extends TestCase
{
    use Traits\CollapsedBroadcastTrait;

    public function testMappingSubcategories()
    {
        $genre = new Genre([0], 'c00001', 'Category1', 'cat1');
        $subgenre = [new Genre([0, 1], 'c00002', 'Category2', 'cat2')];

        $expected = (object) [
            'category' => (object) [
                'type' => 'genre',
                'id' => 'c00001',
                'key' => 'cat1',
                'title' => 'Category1',
                'narrower' => [
                    (object) [
                        'type' => 'genre',
                        'id' => 'c00002',
                        'key' => 'cat2',
                        'title' => 'Category2',
                        'has_topic_page' => false,
                        'sameAs' => null,
                    ],
                ],
                'broader' => (object) [],
                'has_topic_page' => false,
                'sameAs' => null,
            ],
            'available_programmes_count' => 0,
            'available_programmes' => null,
            'upcoming_broadcasts_count' => 0,
            'upcoming_broadcasts' => null,
            'available_and_upcoming_counts' => [],
        ];

        $mapper = new CategoryMetadataMapper();
        $this->assertEquals($mapper->getApsObject($genre, $subgenre), $expected);
    }
}
