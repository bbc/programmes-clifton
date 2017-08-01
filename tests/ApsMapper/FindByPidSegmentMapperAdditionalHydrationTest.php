<?php
namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\FindByPidSegmentMapper;
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

class FindByPidSegmentMapperAdditionalHydrationTest extends TestCase
{
    public function testMappingContributions()
    {
        $segment = $this->createMock(Segment::class);
        $episode = $this->createMock(Episode::class);

        $contributions = [
            new Contribution(
                new Pid('p012djw9'),
                new Contributor(0, new Pid('p012djx0'), '', '', null, null, null, '8e05a404-3f8d-4b0a-9fc2-b7ab821b75f0'),
                $episode,
                ''
            ),
        ];

        $segment->method('getContributions')->willReturn($contributions);

        $expectedContributions = [
            (object) [
                'pid' => 'p012djx0',
                'name' => '',
                'role' => '',
                'musicbrainz_gid' => '8e05a404-3f8d-4b0a-9fc2-b7ab821b75f0',
            ],
        ];

        $mapper = new FindByPidSegmentMapper();
        $apsObject = $mapper->getApsObject($segment, []);

        $this->assertObjectHasAttribute('segment_events', $apsObject);
        $this->assertEquals($expectedContributions, $apsObject->contributions);
    }

    public function testMappingSegmentEvents()
    {
        $segment = $this->createMock(Segment::class);

        $segmentEvents = [
            new SegmentEvent(
                new Pid('p002glwz'),
                new Version(
                    0,
                    new Pid('b00hvw0s'),
                    new Episode( //Programme
                        [0],
                        new Pid('b00hvw8w'),
                        '',
                        '',
                        new Synopses('', '', ''),
                        new Image(new Pid('p01gdp18'), '', '', '', '', ''),
                        0,
                        0,
                        false,
                        false,
                        false,
                        22,
                        '',
                        0,
                        0,
                        0,
                        0
                    ),
                    false,
                    false,
                    0,
                    22
                ),
                $segment,
                new Synopses('', '', '')
            ),
        ];

        $expectedSegmentEvents = [
            (object) [
                'pid' =>  'p002glwz',
                'title' => null,
                'short_synopsis' => '',
                'medium_synopsis' => '',
                'long_synopsis' => '',
                'version_offset' => null,
                'position' => null,
                'is_chapter' => false,
                'version' => (object) [
                    'pid' => 'b00hvw0s',
                    'duration' => null,
                    'programme' => (object) [
                        'type' => 'episode',
                        'pid' => 'b00hvw8w',
                        'title' => 'Untitled',
                        'image' => (object) [
                            'pid' => 'p01gdp18',
                        ],
                        'short_synopsis' => '',
                        'media_type' => null,
                    ],
                ],
            ],
        ];

        $mapper = new FindByPidSegmentMapper();
        $apsObject = $mapper->getApsObject($segment, $segmentEvents);

        $this->assertObjectHasAttribute('segment_events', $apsObject);
        $this->assertEquals($expectedSegmentEvents, $apsObject->{'segment_events'});
    }

    public function testMappingPrimaryContributor()
    {
        $segment = $this->createMock(Segment::class);
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

        $segment->method('getContributions')->willReturn($contributions);

        $mapper = new FindByPidSegmentMapper();
        $apsObject = $mapper->getApsObject($segment, []);

        $this->assertObjectHasAttribute('primary_contributor', $apsObject);
        $this->assertEquals($expectedPrimaryContributor, $apsObject->{'primary_contributor'});
    }

    public function testMappingOwnership()
    {
        $segment = $this->createMock(Segment::class);
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
            'service' => (object) [
                'type' => null,
                'id' => 'bbc_1xtra',
                'key' => '1xtra',
                'title' => 'BBC Radio 1Xtra',
            ],
        ];

        $mapper = new FindByPidSegmentMapper();
        $apsObject = $mapper->getApsObject($segment, [$segmentEvent]);

        $this->assertObjectHasAttribute('segment_events', $apsObject);
        $this->assertEquals($expectedOwnership, $apsObject->{'segment_events'}[0]->version->programme->ownership);
    }
}
