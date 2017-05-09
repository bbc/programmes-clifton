<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\CategoryMetadataMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Format;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use PHPUnit\Framework\TestCase;

class CategoryMetadataMapperTest extends TestCase
{
    public function testMappingGenreWithParentMetadata()
    {
        $genre = new Genre([0, 1], 'c00001', 'Category1', 'cat1', new Genre([0], 'c00002', 'Category2', 'cat2'));
        $expected = (object) [
            'category' => (object) [
                'type' => 'genre',
                'id' => 'c00001',
                'key' => 'cat1',
                'title' => 'Category1',
                'broader' => (object) [
                    'category' => (object) [
                        'type' => 'genre',
                        'id' => 'c00002',
                        'key' => 'cat2',
                        'title' => 'Category2',
                        'broader' => (object) [],
                        'has_topic_page' => false,
                        'sameAs' => null,
                    ],
                ],
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
        $this->assertEquals($expected, $mapper->getApsObject($genre));
    }

    public function testMappingFormat()
    {
        $genre = new Format([0], 'f00001', 'Format', 'fat1');
        $expected = (object) [
            'category' => (object) [
                'type' => 'format',
                'id' => 'f00001',
                'key' => 'fat1',
                'title' => 'Format',
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
        $this->assertEquals($expected, $mapper->getApsObject($genre));
    }
}
