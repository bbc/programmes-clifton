<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\CategoryMetadataMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

class CategoryMetadataMapperAdditionalHydrationTest extends PHPUnit_Framework_TestCase
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

    public function testMappingTvMedium()
    {
        $genre = new Genre([0], 'c00001', 'Category1', 'cat1');

        $expected = (object) [
            'category' => (object) [
                'type' => 'genre',
                'id' => 'c00001',
                'key' => 'cat1',
                'title' => 'Category1',
                'broader' => (object) [],
                'has_topic_page' => false,
                'sameAs' => null,
            ],
            'service' => (object) [
                'key' => 'tv',
                'id' => 'tv',
                'title' => 'BBC TV',
            ],
            'available_programmes_count' => 0,
            'available_programmes' => null,
            'upcoming_broadcasts_count' => 0,
            'upcoming_broadcasts' => null,
            'available_and_upcoming_counts' => [],
        ];

        $mapper = new CategoryMetadataMapper();
        $this->assertEquals($expected, $mapper->getApsObject($genre, null, 0, 0, [], 'tv'));
    }

    public function testMappingRadioMedium()
    {
        $genre = new Genre([0], 'c00001', 'Category1', 'cat1');

        $expected = (object) [
            'category' => (object) [
                'type' => 'genre',
                'id' => 'c00001',
                'key' => 'cat1',
                'title' => 'Category1',
                'broader' => (object) [],
                'has_topic_page' => false,
                'sameAs' => null,
            ],
            'service' => (object) [
                'key' => 'radio',
                'id' => 'radio',
                'title' => 'BBC Radio',
            ],
            'available_programmes_count' => 0,
            'available_programmes' => null,
            'upcoming_broadcasts_count' => 0,
            'upcoming_broadcasts' => null,
            'available_and_upcoming_counts' => [],
        ];

        $mapper = new CategoryMetadataMapper();
        $this->assertEquals($expected, $mapper->getApsObject($genre, null, 0, 0, [], 'radio'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMappingInvalidMedium()
    {
        $genre = new Genre([0], 'c00001', 'Category1', 'cat1');

        $mapper = new CategoryMetadataMapper();
        $mapper->getApsObject($genre, null, 0, 0, [], 'wibble');
    }

    private function formatDateTime(DateTimeImmutable $dateTimeImmutable): string
    {
        $dateTimeImmutable = $dateTimeImmutable->setTimezone(new \DateTimeZone('Europe/London'));
        if ($dateTimeImmutable->getOffset()) {
            // 2002-10-19T21:00:00+01:00
            return $dateTimeImmutable->format(DATE_ATOM);
        } else {
            // 2016-02-01T21:00:00Z
            return $dateTimeImmutable->format('Y-m-d\TH:i:s\Z');
        }
    }
}
