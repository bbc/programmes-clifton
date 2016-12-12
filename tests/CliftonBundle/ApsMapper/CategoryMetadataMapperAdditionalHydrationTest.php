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

    public function testMappingAvailableEpisodes()
    {
        $streamableFrom = new DateTimeImmutable("2014-06-20 10:45 Europe/London");
        $streamableUntil = DateTimeImmutable::createFromMutable((new DateTime())->add(new DateInterval('PT10S')));

        $genre = new Genre([0], 'c00001', 'Category1', 'cat1');
        $episodes = [$this->createEpisode($streamableFrom, $streamableUntil)];

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
            'available_programmes_count' => 1,
            'available_programmes' => [
                (object) [
                    'type' => 'episode',
                    'pid' => 'b06tl32t',
                    'position' => 2101,
                    'title' => 'The Husbands of River Song',
                    'short_synopsis' => 'Short Synopsis',
                    'media_type' => 'audio_video',
                    'duration' => 2201,
                    'image' => (object) [
                        'pid' => 'p01m5mss',
                    ],
                    'display_titles' => (object) [
                        'title' => 'The Husbands of River Song',
                        'subtitle' => '',
                    ],
                    'first_broadcast_date' => '2000-01-01T00:00:00Z',
                    'has_medium_or_long_synopsis' => true,
                    'has_related_links' => true,
                    'has_clips' => true,
                    'has_segment_events' => true,
                    'segments_title' => '',
                    'available_until' => $this->formatDateTime($streamableUntil),
                    'actual_start' => '2014-06-20T10:45:00+01:00',
                    'is_available_mediaset_pc_sd' => true,
                    'is_legacy_media' => false,
                    'media' => (object) [
                        'format' => 'video',
                        'expires' => $this->formatDateTime($streamableUntil),
                        'availability' => '0 minute left to watch',
                    ],
                ],
            ],
            'upcoming_broadcasts_count' => 0,
            'upcoming_broadcasts' => null,
            'available_and_upcoming_counts' => [],
        ];

        $mapper = new CategoryMetadataMapper();
        $this->assertEquals($expected, $mapper->getApsObject($genre, null, $episodes, count($episodes)));
    }

    public function testMappingUpcomingBroadcasts()
    {
        $genre = new Genre([0], 'c00001', 'Category1', 'cat1');

        $network = $this->createNetwork(1);
        $service1 = $this->createService($network);

        $version = $this->createMock(Version::CLASS);

        $streamableFrom = new DateTimeImmutable("2014-06-20 10:45 Europe/London");
        $streamableUntil = DateTimeImmutable::createFromMutable((new DateTime())->add(new DateInterval('PT10S')));

        $episode = $this->createEpisode($streamableFrom, $streamableUntil);
        $broadcasts = [$this->createBroadcast($version, $episode, [$service1])];

        $expectedResult = (object) [
            'category' => (object) [
                'type' => 'genre',
                'id' => 'c00001',
                'key' => 'cat1',
                'title' => 'Category1',
                'broader' => (object) [],
                'has_topic_page' => false,
                'sameAs' => null,
            ],
            'available_programmes_count' => 0,
            'available_programmes' => null,
            'upcoming_broadcasts_count' => 1,
            'upcoming_broadcasts' => [
                (object) [
                    'is_repeat' => true,
                    'is_blanked' => true,
                    'schedule_date' => '2014-06-20',
                    'start' => '2014-06-20T11:45:00+01:00',
                    'end' => '2014-06-20T12:45:00+01:00',
                    'duration' => 5400,
                    'service' => (object) [
                        'type' => 'tv',
                        'id' => 'network_1',
                        'key' => 'network1',
                        'title' => 'Network 1',
                        'outlets' => [
                            (object) [
                                'id' => 'service0',
                                'key' => 'service0_url_key',
                                'title' => 'Short name service0',
                            ],
                        ],
                    ],
                    'programme' => (object) [
                        'type' => 'episode',
                        'pid' => 'b06tl32t',
                        'position' => 2101,
                        'title' => 'The Husbands of River Song',
                        'short_synopsis' => 'Short Synopsis',
                        'media_type' => 'audio_video',
                        'duration' => 2201,
                        'image' => (object) [
                            'pid' => 'p01m5mss',
                        ],
                        'display_titles' => (object) [
                            'title' => 'The Husbands of River Song',
                            'subtitle' => '',
                        ],
                        'first_broadcast_date' => '2000-01-01T00:00:00Z',
                        'available_until' => $this->formatDateTime($streamableUntil),
                        'actual_start' => '2014-06-20T10:45:00+01:00',
                        'is_available_mediaset_pc_sd' => true,
                        'is_legacy_media' => false,
                        'media' => (object) [
                            'format' => 'video',
                            'expires' => $this->formatDateTime($streamableUntil),
                            'availability' => '0 minute left to watch',
                        ],
                    ],
                ],
            ],
            'available_and_upcoming_counts' => [],
        ];

        $mapper = new CategoryMetadataMapper();
        $apsObject = $mapper->getApsObject($genre, null, null, 0, $broadcasts, count($broadcasts));

        $this->assertEquals($expectedResult, $apsObject);
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
        $this->assertEquals($expected, $mapper->getApsObject($genre, null, null, 0, null, 0, [], 'tv'));
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
        $this->assertEquals($expected, $mapper->getApsObject($genre, null, null, 0, null, 0, [], 'radio'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMappingInvalidMedium()
    {
        $genre = new Genre([0], 'c00001', 'Category1', 'cat1');

        $mapper = new CategoryMetadataMapper();
        $mapper->getApsObject($genre, null, null, 0, null, 0, [], 'wibble');
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
