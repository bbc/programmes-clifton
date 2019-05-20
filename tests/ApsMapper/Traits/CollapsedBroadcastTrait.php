<?php

namespace Tests\BBC\CliftonBundle\ApsMapper\Traits;

use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Options;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\Nid;
use BBC\ProgrammesPagesService\Domain\ValueObject\PartialDate;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Sid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use DateTimeImmutable;

trait CollapsedBroadcastTrait
{
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
            false,
            new Options(),
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
            new Options(),
            'network' . $id,
            'audio',
            NetworkMediumEnum::TV
        );
    }

    private function createService($network = null, $id = 'service0', $pid = 'b0000001')
    {
        return new Service(
            0,
            new Sid($id),
            new Pid($pid),
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
            true,
            new Options()
        );
    }

    private function createBroadcast($episode, $services)
    {
        return new CollapsedBroadcast(
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
