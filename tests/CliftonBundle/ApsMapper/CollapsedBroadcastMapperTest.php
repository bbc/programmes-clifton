<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\CollapsedBroadcastMapper;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\Nid;
use BBC\ProgrammesPagesService\Domain\ValueObject\PartialDate;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Sid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use PHPUnit_Framework_TestCase;
use InvalidArgumentException;

class CollapsedBroadcastMapperTest extends PHPUnit_Framework_TestCase
{
    /** @dataProvider generatingRemainingTimeDataProvider */
    public function testGeneratingRemainingTime($modify, $result)
    {
        $mapper = new CollapsedBroadcastMapper();

        $network = $this->createNetwork(1);
        $service = $this->createService($network);

        $version = $this->createMock(Version::CLASS);

        $episode = $this->createEpisode(
            new DateTimeImmutable("2014-06-20 10:45 Europe/London"),
            (new DateTimeImmutable())->modify($modify)
        );

        $broadcast = $this->createBroadcast($version, $episode, [$service]);

        $this->assertEquals(
            $mapper->getApsObject($broadcast)->{'programme'}->{'media'}->{'availability'},
            $result
        );
    }

    public function generatingRemainingTimeDataProvider()
    {
        return [
            ['+1 minute', '1 minute left to watch'],
            ['+1 hour', '1 hour left to watch'],
            ['+1 day', '1 day left to watch'],
            ['+1 month', '1 month left to watch'],
            ['+2 hours', '2 hours left to watch'],
            ['+401 day', 'Available to watch'],
        ];
    }

    public function testMappingCollapsedBroadcastForMonth()
    {
        $network = $this->createNetwork(1);
        $service1 = $this->createService($network);

        $version = $this->createMock(Version::CLASS);

        $streamableFrom = new DateTimeImmutable("2014-06-20 10:45 Europe/London");
        $streamableUntil = DateTimeImmutable::createFromMutable((new DateTime())->add(new DateInterval('PT10S')));

        $episode = $this->createEpisode($streamableFrom, $streamableUntil);
        $broadcast1 = $this->createBroadcast($version, $episode, [$service1]);

        $expectedResult = (object) [
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
        ];

        $mapper = new CollapsedBroadcastMapper();
        $apsObject = $mapper->getApsObject($broadcast1);

        $this->assertEquals($expectedResult, $apsObject);
    }

    public function testMappingCollapsedBroadcastForMonthWithMultipleServices()
    {

        $network = $this->createNetwork(1);
        $service1 = $this->createService($network, 'service1');
        $service2 = $this->createService($network, 'service2');

        $version = $this->createMock(Version::CLASS);

        $streamableFrom = new DateTimeImmutable("2014-06-20 10:45 Europe/London");
        $streamableUntil = DateTimeImmutable::createFromMutable((new DateTime())->add(new DateInterval('PT10S')));

        $episode = $this->createEpisode($streamableFrom, $streamableUntil);
        $broadcast1 = $this->createBroadcast($version, $episode, [$service1, $service2]);

        $expectedResult = (object) [
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
        ];

        $mapper = new CollapsedBroadcastMapper();
        $apsObject = $mapper->getApsObject($broadcast1);

        $this->assertEquals($expectedResult, $apsObject);
    }

    public function testMappingCollapsedBroadcastForMonthWithProgrammeParent()
    {
        $network = $this->createNetwork(1);
        $service1 = $this->createService($network, 'service1');
        $version = $this->createMock(Version::CLASS);

        $streamableFrom = new DateTimeImmutable("2014-06-20 10:45 Europe/London");
        $streamableUntil = DateTimeImmutable::createFromMutable((new DateTime())->add(new DateInterval('PT10S')));

        $series = $this->createSeries(1);
        $episode = $this->createEpisode($streamableFrom, $streamableUntil, $series);
        $broadcast1 = $this->createBroadcast($version, $episode, [$service1]);

        $expectedResult = (object) [
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

        $version = $this->createMock(Version::CLASS);

        $streamableFrom = new DateTimeImmutable("2014-06-20 10:45 Europe/London");
        $streamableUntil = DateTimeImmutable::createFromMutable((new DateTime())->add(new DateInterval('PT10S')));

        $episode = $this->createEpisode($streamableFrom, $streamableUntil);
        $broadcast1 = $this->createBroadcast($version, $episode, $services);

        $mapper = new CollapsedBroadcastMapper();
        $apsObject = $mapper->getApsObject($broadcast1);

        $expectedSids = ['service1'];

        $this->assertEquals($expectedSids, array_column($apsObject->service->outlets, 'id'));
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

    private function createEpisode($streamableFrom, $streamableUntil, $series = null, $ancestry = [0, 1])
    {
        return new Episode(
            $ancestry,
            new Pid('b06tl32t'),
            'The Husbands of River Song',
            'Search Title',
            new Synopses('Short Synopsis', 'Medium Synopsis', ' '),
            new Image(new Pid('p01m5mss'), 'Title', 'ShortSynopsis', 'ShortSynopsis', 'standard', 'jpg'),
            1101,
            1102,
            true,
            true,
            false,
            1103,
            MediaTypeEnum::VIDEO,
            1201,
            1301,
            1302,
            1303,
            $series,
            2101,
            null,
            [],
            [],
            new DateTimeImmutable('2000-01-01 00:00:00'),
            new PartialDate(2015, 02, 00),
            2201,
            $streamableFrom,
            $streamableUntil
        );
    }

    private function createNetwork($id = 0)
    {
        return new Network(
            new Nid('network_' . $id),
            'Network ' . $id,
            new Image(
                new Pid('p0000000'),
                'Image',
                'Short image synopsis',
                'Long image synopsis',
                'type',
                'jpg'
            ),
            'network' . $id,
            'audio',
            NetworkMediumEnum::TV
        );
    }

    private function createService($network = null, $id = 'service0')
    {
        return new Service(
            0,
            new Sid($id),
            'Service ' . $id,
            'Short name ' . $id,
            $id . '_url_key',
            $network
        );
    }

    private function createSeries($id = 0)
    {
        return new Series(
            [$id],
            new Pid('p0000001'),
            'Series ' . $id,
            'Series',
            new Synopses('Short Synopsis', 'Medium Synopsis', ' '),
            new Image(new Pid('p01m5mss'), 'Title', 'ShortSynopsis', 'ShortSynopsis', 'standard', 'jpg'),
            1101,
            1102,
            true,
            true,
            true,
            1103,
            1,
            1,
            0,
            1,
            1,
            true
        );
    }

    private function createBroadcast($version, $episode, $services)
    {
        return new CollapsedBroadcast(
            $version,
            $episode,
            $services,
            $streamableFrom = new DateTimeImmutable("2014-06-20 11:45 Europe/London"),
            $streamableFrom = new DateTimeImmutable("2014-06-20 12:45 Europe/London"),
            5400,
            true,
            true
        );
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
