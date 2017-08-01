<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\FindByPidSegmentEventMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\Entity\Contributor;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\MasterBrand;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\Mid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Nid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use PHPUnit\Framework\TestCase;

class FindByPidSegmentEventMapperAdditionalHydrationTest extends TestCase
{
    public function testMappingPrimaryContributor()
    {
        $episode = $this->createMock(Episode::class);

        $contributions = [
            new Contribution(
                new Pid('p01w0t6b'),
                new Contributor(
                    0,
                    new Pid('p00sx484'),
                    '',
                    'Cornershop',
                    null,
                    null,
                    null,
                    '92046be7-0927-4835-a4ed-a90416747d53'
                ),
                $episode,
                'Performer'
            ),
        ];

        $expectedPrimaryContributor = (object) [
            'pid' => 'p00sx484',
            'name' => 'Cornershop',
            'sort_name' => null,
            'musicbrainz_gid' => '92046be7-0927-4835-a4ed-a90416747d53',
        ];

        $segment = $this->createMock(Segment::class);
        $segmentEvent = $this->createMock(SegmentEvent::class);
        $version = $this->createMock(Version::class);
        $version->method('getProgrammeItem')->willReturn($this->createMock(Episode::class));
        $segmentEvent->method('getSegment')->willReturn($segment);
        $segmentEvent->method('getVersion')->willReturn($version);
        $segment->method('getContributions')->willReturn($contributions);

        $mapper = new FindByPidSegmentEventMapper();
        $apsObject = $mapper->getApsObject($segmentEvent);

        $this->assertObjectHasAttribute('segment', $apsObject);
        $this->assertObjectHasAttribute('primary_contributor', $apsObject->{'segment'});
        $this->assertEquals($expectedPrimaryContributor, $apsObject->{'segment'}->{'primary_contributor'});
    }

    public function testMappingContributions()
    {
        $episode = $this->createMock(Episode::class);
        $contributions = [
            new Contribution(
                new Pid('p0000000'),
                new Contributor(
                    0,
                    new Pid('p0000001'),
                    'person',
                    'name 1',
                    null,
                    null,
                    null,
                    '92046be7-0927-4835-a4ed-a90416747d53'
                ),
                $episode,
                'PERFORMER'
            ),
            new Contribution(
                new Pid('p0000002'),
                new Contributor(
                    1,
                    new Pid('p0000003'),
                    'person',
                    'name 2',
                    null,
                    null,
                    null,
                    '6386ddff-0d13-4685-9f0a-a82bf022fb1c'
                ),
                $episode,
                'ANCHOR'
            ),
            new Contribution(
                new Pid('p0000004'),
                new Contributor(
                    2,
                    new Pid('p0000005'),
                    'person',
                    'name 3',
                    null,
                    null,
                    null,
                    'ce2acb80-9a52-40a3-9b8a-5171de3e2fed'
                ),
                $episode,
                'ANIMATOR'
            ),
        ];

        $expectedContributions = [
            (object) [
                'pid' => 'p0000001',
                'name' => 'name 1',
                'role' => 'PERFORMER',
                'musicbrainz_gid' => '92046be7-0927-4835-a4ed-a90416747d53',
            ],
            (object) [
                'pid' => 'p0000003',
                'name' => 'name 2',
                'role' => 'ANCHOR',
                'musicbrainz_gid' => '6386ddff-0d13-4685-9f0a-a82bf022fb1c',
            ],
            (object) [
                'pid' => 'p0000005',
                'name' => 'name 3',
                'role' => 'ANIMATOR',
                'musicbrainz_gid' => 'ce2acb80-9a52-40a3-9b8a-5171de3e2fed',
            ],
        ];

        $segmentEvent = $this->createMock(SegmentEvent::class);
        $segment = $this->createMock(Segment::class);
        $version = $this->createMock(Version::class);
        $segment->method('getContributions')->willReturn($contributions);
        $version->method('getProgrammeItem')->willReturn($episode);
        $segmentEvent->method('getSegment')->willReturn($segment);
        $segmentEvent->method('getVersion')->willReturn($version);

        $mapper = new FindByPidSegmentEventMapper();
        $apsObject = $mapper->getApsObject($segmentEvent);

        $this->assertObjectHasAttribute('segment', $apsObject);
        $this->assertObjectHasAttribute('contributions', $apsObject->{'segment'});
        $this->assertEquals($expectedContributions, $apsObject->{'segment'}->{'contributions'});
    }

    public function testMappingSegmentEventsBySegment()
    {
        $version = $this->createMock(Version::class);
        $segment = $this->createMock(Segment::class);

        $segmentEventsBySegment = [
            new SegmentEvent(
                new Pid('p0000000'),
                $version,
                $segment,
                new Synopses('1 short', '1 medium', '1 long'),
                'segev 1',
                true,
                1,
                2
            ),
            new SegmentEvent(
                new Pid('p0000001'),
                $version,
                $segment,
                new Synopses('2 short', '2 medium', '2 long'),
                'segev 2',
                false,
                3,
                4
            ),
            new SegmentEvent(
                new Pid('p0000002'),
                $version,
                $segment,
                new Synopses('3 short', '3 medium', '3 long'),
                'segev 3',
                true,
                5,
                6
            ),
        ];

        $expectedSegmentEventsBySegment = [
            (object) [
                'pid' => 'p0000000',
                'title' => 'segev 1',
                'short_synopsis' => '1 short',
                'medium_synopsis' => '1 medium',
                'long_synopsis' => '1 long',
                'version_offset' => 1,
                'position' => 2,
                'is_chapter' => true,
            ],
            (object) [
                'pid' => 'p0000001',
                'title' => 'segev 2',
                'short_synopsis' => '2 short',
                'medium_synopsis' => '2 medium',
                'long_synopsis' => '2 long',
                'version_offset' => 3,
                'position' => 4,
                'is_chapter' => false,
            ],
            (object) [
                'pid' => 'p0000002',
                'title' => 'segev 3',
                'short_synopsis' => '3 short',
                'medium_synopsis' => '3 medium',
                'long_synopsis' => '3 long',
                'version_offset' => 5,
                'position' => 6,
                'is_chapter' => true,
            ],
        ];

        $segmentEvent = $this->createMock(SegmentEvent::class);
        $version->method('getProgrammeItem')->willReturn($this->createMock(Episode::class));
        $segmentEvent->method('getSegment')->willReturn($segment);
        $segmentEvent->method('getVersion')->willReturn($version);
        $mapper = new FindByPidSegmentEventMapper();
        $apsObject = $mapper->getApsObject($segmentEvent, $segmentEventsBySegment);

        $this->assertObjectHasAttribute('segment', $apsObject);
        $this->assertObjectHasAttribute('segment_events', $apsObject->{'segment'});
        $this->assertEquals($expectedSegmentEventsBySegment, $apsObject->{'segment'}->{'segment_events'});
    }

    public function testMappingOwnership()
    {
        $segmentEvent = $this->createMock(SegmentEvent::class);
        $version = $this->createMock(Version::class);
        $episode = $this->createMock(Episode::class);

        $masterBrand = new MasterBrand(
            new Mid('bbc_1xtra'),
            'BBC Radio 1Xtra',
            new Image(new Pid('p01gdp18'), '', '', '', '', ''),
            new Network(
                new Nid('bbc_1xtra'),
                'BBC Radio 1Xtra',
                new Image(new Pid('p01gdp18'), '', '', '', '', ''),
                '1xtra',
                'National Radio'
            )
        );

        $segmentEvent->method('getVersion')->willReturn($version);
        $version->method('getProgrammeItem')->willReturn($episode);
        $episode->method('getMasterBrand')->willReturn($masterBrand);

        $expectedOwnership = (object) [
            'ownership' => (object) [
                'service' => (object) [
                    'type' => null,
                    'id' => 'bbc_1xtra',
                    'key' => '1xtra',
                    'title' => 'BBC Radio 1Xtra',
                ],
            ],
        ];

        $mapper = new FindByPidSegmentEventMapper();
        $apsObject = $mapper->getApsObject($segmentEvent);

        $this->assertObjectHasAttribute('version', $apsObject);
        $this->assertEquals($expectedOwnership, $apsObject->{'version'}->{'ownership'});
    }
}
