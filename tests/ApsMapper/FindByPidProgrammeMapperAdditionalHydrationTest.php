<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;

use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\RelatedLink;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\Entity\VersionType;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\PartialDate;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use BBC\CliftonBundle\ApsMapper\FindByPidProgrammeMapper;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class FindByPidProgrammeMapperAdditionalHydrationTest extends TestCase
{
    public function testMappingRelatedLinks()
    {
        $series = $this->createMock(Series::class);

        $relatedLink = new RelatedLink(
            'Title',
            'http://www.example.com',
            'Short Synopsis',
            'Long Synosis',
            'standard',
            false
        );

        $expectedLinks = [
            (object) [
                'type' => 'standard',
                'title' => 'Title',
                'url' => 'http://www.example.com',
            ],
        ];

        $mapper = new FindByPidProgrammeMapper();
        $apsObject = $mapper->getApsObject($series, [$relatedLink]);

        $this->assertObjectHasAttribute('links', $apsObject);
        $this->assertEquals($expectedLinks, $apsObject->links);
    }

    public function testMappingPeers()
    {
        $streamableFrom = new DateTimeImmutable();
        $streamableUntil = new DateTimeImmutable();

        $brand = $this->createMock(Brand::class);
        $series = $this->createMock(Series::class);
        $series->method('getParent')->willReturn($brand);

        $previousSibling = new Series(
            [1],
            new Pid('b06hgxtt'),
            'Series 9 - Omnibus',
            'Search Title',
            new Synopses('Short Synopsis', 'Medium Synopsis', 'Long Synopsis'),
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
            null,
            2101,
            null,
            [],
            [],
            new DateTimeImmutable('2000-01-01 00:00:00'),
            2201
        );

        $nextSibling = new Episode(
            [1],
            new Pid('b06tl32t'),
            'The Husbands of River Song',
            'Search Title',
            new Synopses('Short Synopsis', 'Medium Synopsis', 'Long Synopsis'),
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
            null,
            2101,
            null,
            [],
            [],
            new DateTimeImmutable('2000-01-01 01:00:00'),
            new PartialDate(2015, 02, 00),
            2201,
            $streamableFrom,
            $streamableUntil
        );

        $expectedPeers = (object) [
            'previous' => (object) [
                'type' => 'series',
                'title' => 'Series 9 - Omnibus',
                'pid' => 'b06hgxtt',
                'first_broadcast_date' => '2000-01-01T00:00:00Z',
                'position' => 2101,
                'media_type' => null,
            ],
            'next' => (object) [
                'type' => 'episode',
                'title' => 'The Husbands of River Song',
                'pid' => 'b06tl32t',
                'first_broadcast_date' => '2000-01-01T01:00:00Z',
                'position' => 2101,
                'media_type' => 'audio_video',
            ],
        ];

        $mapper = new FindByPidProgrammeMapper();
        $apsObject = $mapper->getApsObject($series, [], $nextSibling, $previousSibling);

        $this->assertObjectHasAttribute('peers', $apsObject);
        $this->assertEquals($expectedPeers, $apsObject->peers);
    }

    public function testMappingVersions()
    {
        $episode = $this->createMock(Episode::class);

        $versions = [
            new Version(
                0,
                new Pid('v0000001'),
                $episode,
                true,
                false,
                0,
                22,
                100,
                'Guidance Warning',
                true,
                new DateTimeImmutable('2016-01-01 00:00:00'),
                new DateTimeImmutable('2055-01-01 00:00:00'),
                [
                    new VersionType('Original', 'Original version'),
                    new VersionType('other', 'Other'),
                ]
            ),
            new Version(
                0,
                new Pid('v0000002'),
                $episode,
                false,
                false,
                0,
                22,
                200,
                'Guidance Warning',
                true,
                null,
                null,
                [
                    new VersionType('DubbedAudioDescribed', 'Dubbed Audio Described'),
                    new VersionType('Legal', 'Legal'),
                ]
            ),
        ];

        $expectedVersions = [
            (object) [
                'canonical' => 1,
                'pid' => 'v0000001',
                'duration' => 100,
                'types' => ['Original version', 'Other'],
            ],
            (object) [
                'canonical' => 0,
                'pid' => 'v0000002',
                'duration' => 200,
                'types' => ['Dubbed Audio Described', 'Legal'],
            ],
        ];

        $mapper = new FindByPidProgrammeMapper();
        $apsObject = $mapper->getApsObject($episode, [], null, null, $versions);

        $this->assertObjectHasAttribute('versions', $apsObject);
        $this->assertEquals($expectedVersions, $apsObject->versions);
    }
}
