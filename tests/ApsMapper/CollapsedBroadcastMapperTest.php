<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\CollapsedBroadcastMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class CollapsedBroadcastMapperTest extends TestCase
{
    use Traits\CollapsedBroadcastTrait;

    /** @dataProvider generatingRemainingTimeDataProvider */
    public function testGeneratingRemainingTime($modify, $result)
    {
        $mapper = new CollapsedBroadcastMapper();

        $network = $this->createNetwork(1);
        $service = $this->createService($network);

        $episode = $this->createEpisode(
            new DateTimeImmutable("2014-06-20 10:45 Europe/London"),
            (new DateTimeImmutable())->modify($modify)
        );

        $broadcast = $this->createBroadcast($episode, [$service]);

        $this->assertEquals(
            $mapper->getApsObject($broadcast)[0]->{'programme'}->{'media'}->{'availability'},
            $result
        );
    }

    public function generatingRemainingTimeDataProvider()
    {
        return [
            ['+1 minute', '1 minute left to watch'],
            ['+1 hour', '1 hour left to watch'],
            ['+1 day', '1 day left to watch'],
            ['+30 days', '1 month left to watch'],
            ['+2 hours', '2 hours left to watch'],
            ['+401 day', 'Available to watch'],
        ];
    }

    public function testMappingCollapsedBroadcast()
    {
        $network = $this->createNetwork(1);
        $service1 = $this->createService($network);

        $streamableFrom = new DateTimeImmutable("2014-06-20 10:45 Europe/London");
        $streamableUntil = DateTimeImmutable::createFromMutable((new DateTime())->add(new DateInterval('PT10S')));

        $episode = $this->createEpisode($streamableFrom, $streamableUntil);
        $broadcast1 = $this->createBroadcast($episode, [$service1]);

        $expectedResult = [
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
        ];

        $mapper = new CollapsedBroadcastMapper();
        $apsObject = $mapper->getApsObject($broadcast1);

        $this->assertEquals($expectedResult, $apsObject);
    }

    public function testMappingCollapsedBroadcastWithMultipleServices()
    {

        $network = $this->createNetwork(1);
        $service1 = $this->createService($network, 'service1');
        $service2 = $this->createService($network, 'service2');

        $streamableFrom = new DateTimeImmutable("2014-06-20 10:45 Europe/London");
        $streamableUntil = DateTimeImmutable::createFromMutable((new DateTime())->add(new DateInterval('PT10S')));

        $episode = $this->createEpisode($streamableFrom, $streamableUntil);
        $broadcast1 = $this->createBroadcast($episode, [$service1, $service2]);

        $expectedResult = [
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
                            'id' => 'service1',
                            'key' => 'service1_url_key',
                            'title' => 'Short name service1',
                        ],
                        (object) [
                            'id' => 'service2',
                            'key' => 'service2_url_key',
                            'title' => 'Short name service2',
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
        ];

        $mapper = new CollapsedBroadcastMapper();
        $apsObject = $mapper->getApsObject($broadcast1);

        $this->assertEquals($expectedResult, $apsObject);
    }

    public function testMappingCollapsedBroadcastWithMultipleServicesWithDifferentNetworks()
    {

        $network = $this->createNetwork(1);
        $network2 = $this->createNetwork(2);
        $service1 = $this->createService($network, 'service1');
        $service2 = $this->createService($network2, 'service2');

        $streamableFrom = new DateTimeImmutable("2014-06-20 10:45 Europe/London");
        $streamableUntil = DateTimeImmutable::createFromMutable((new DateTime())->add(new DateInterval('PT10S')));

        $episode = $this->createEpisode($streamableFrom, $streamableUntil);
        $broadcast1 = $this->createBroadcast($episode, [$service1, $service2]);

        $expectedResult = [
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
                            'id' => 'service1',
                            'key' => 'service1_url_key',
                            'title' => 'Short name service1',
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
            (object) [
                'is_repeat' => true,
                'is_blanked' => true,
                'schedule_date' => '2014-06-20',
                'start' => '2014-06-20T11:45:00+01:00',
                'end' => '2014-06-20T12:45:00+01:00',
                'duration' => 5400,
                'service' => (object) [
                    'type' => 'tv',
                    'id' => 'network_2',
                    'key' => 'network2',
                    'title' => 'Network 2',
                    'outlets' => [
                        (object) [
                            'id' => 'service2',
                            'key' => 'service2_url_key',
                            'title' => 'Short name service2',
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
        ];

        $mapper = new CollapsedBroadcastMapper();
        $apsObject = $mapper->getApsObject($broadcast1);

        $this->assertEquals($expectedResult, $apsObject);
    }

    public function testMappingCollapsedBroadcastWithProgrammeParent()
    {
        $network = $this->createNetwork(1);
        $service1 = $this->createService($network, 'service1');

        $streamableFrom = new DateTimeImmutable("2014-06-20 10:45 Europe/London");
        $streamableUntil = DateTimeImmutable::createFromMutable((new DateTime())->add(new DateInterval('PT10S')));

        $series = $this->createSeries(1);
        $episode = $this->createEpisode($streamableFrom, $streamableUntil, $series);
        $broadcast1 = $this->createBroadcast($episode, [$service1]);

        $expectedResult = [
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
                            'id' => 'service1',
                            'key' => 'service1_url_key',
                            'title' => 'Short name service1',
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
                        'title' => 'Series 1',
                        'subtitle' => 'The Husbands of River Song',
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
                    'programme' => (object) [
                        'type' => 'series',
                        'pid' => 'p0000001',
                        'title' => 'Series 1',
                        'position' => null,
                        'image' => (object) [
                            'pid' => 'p01m5mss',
                        ],
                        'expected_child_count' => null,
                        'first_broadcast_date' => null,
                    ],
                ],
            ],
            ];


        $mapper = new CollapsedBroadcastMapper();
        $apsObject = $mapper->getApsObject($broadcast1);

        $this->assertEquals($expectedResult, $apsObject);
    }

    public function testBlacklistingServices()
    {
        $network = $this->createNetwork(1);
        $services = [
            $this->createService($network, 'service1'),
            $this->createService($network, 'bbc_three_hd'),
            $this->createService($network, 'bbc_four_hd'),
            $this->createService($network, 'cbbc_hd'),
            $this->createService($network, 'cbeebies_hd'),
            $this->createService($network, 'bbc_news_channel_hd'),
        ];

        $streamableFrom = new DateTimeImmutable("2014-06-20 10:45 Europe/London");
        $streamableUntil = DateTimeImmutable::createFromMutable((new DateTime())->add(new DateInterval('PT10S')));

        $episode = $this->createEpisode($streamableFrom, $streamableUntil);
        $broadcast1 = $this->createBroadcast($episode, $services);

        $mapper = new CollapsedBroadcastMapper();
        $apsObject = $mapper->getApsObject($broadcast1);

        $expectedSids = ['service1'];

        $this->assertEquals($expectedSids, array_column($apsObject[0]->service->outlets, 'id'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidDomainObject()
    {
        $image = new Image(new Pid('p01m5mss'), 'Title', 'ShortSynopsis', 'ShortSynopsis', 'standard', 'jpg');

        $mapper = new CollapsedBroadcastMapper();
        $mapper->getApsObject($image);
    }
}
