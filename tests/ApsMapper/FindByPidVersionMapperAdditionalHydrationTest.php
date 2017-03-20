<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\Entity\Contributor;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\MusicSegment;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\CliftonBundle\ApsMapper\FindByPidVersionMapper;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use DateTimeImmutable;
use PHPUnit_Framework_TestCase;

class FindByPidVersionMapperAdditionalHydrationTest extends PHPUnit_Framework_TestCase
{
    public function testMappingContributions()
    {
        $version = $this->createMock(Version::CLASS);
        $episode = $this->createMock(Episode::CLASS);
        $version->method('getProgrammeItem')->willReturn($episode);

        $contributor = new Contributor(
            0,
            new Pid('p02z9mdz'),
            'person',
            'Benjamin Fry',
            null
        );

        $contribution = new Contribution(
            new Pid('p02zc3p4'),
            $contributor,
            $episode,
            'Presenter',
            1,
            null
        );

        $expectedContributors = [
            (object) [
                'character_name' => null,
                'name' => 'Benjamin Fry',
                'family_name' => '',
                'given_name' => '',
                'role' => 'Presenter',
            ],
        ];

        $mapper = new FindByPidVersionMapper();
        $apsObject = $mapper->getApsObject($version, [$contribution]);

        $this->assertObjectHasAttribute('contributors', $apsObject);
        $this->assertEquals($expectedContributors, $apsObject->contributors);
    }

    public function testMappingBroadcasts()
    {
        $version = $this->createMock(Version::CLASS);
        $version->method('getProgrammeItem')->willReturn($this->createMock(Episode::CLASS));

        $service = $this->createMock(Service::CLASS);
        $service->method('getNetwork')->willReturn($this->createMock(Network::CLASS));

        $broadcast = new Broadcast(
            new Pid('b0000001'),
            $version,
            $version->getProgrammeItem(),
            $service,
            new DateTimeImmutable('2015-01-03T00:00:00'),
            new DateTimeImmutable('2015-01-03T01:00:00'),
            180
        );

        $mapper = new FindByPidVersionMapper();
        $apsObject = $mapper->getApsObject($version, [], [], [$broadcast]);

        $expectedBroadcasts = [
            (object) [
                'is_repeat' => false,
                'is_blanked' => false,
                'pid' => 'b0000001',
                'schedule_date' => '2015-01-03',
                'start' => '2015-01-03T00:00:00Z', //APS style formatting
                'end' => '2015-01-03T01:00:00Z',
                'duration' => 180,
                'service' =>
                    (object) [
                        'id' => '',
                        'key' => null,
                        'title' => '',
                    ],
            ],
        ];

        $this->assertObjectHasAttribute('broadcasts', $apsObject);
        $this->assertEquals($expectedBroadcasts, $apsObject->broadcasts);
    }

    public function testMappingSegmentEventsWithContribution()
    {
        $version = $this->createMock(Version::CLASS);
        $episode = $this->createMock(Episode::CLASS);
        $version->method('getProgrammeItem')->willReturn($episode);

        $contributor = new Contributor(
            0,
            new Pid('p02z9mdz'),
            'person',
            'Benjamin Fry',
            null
        );

        $contribution = new Contribution(
            new Pid('p02zc3p4'),
            $contributor,
            $episode,
            'Presenter',
            1,
            null
        );

        $segment = new MusicSegment(
            1,
            new Pid('sg0000001'),
            'Music',
            new Synopses('Segment synopses'),
            22,
            'Segment Title',
            180,
            [
                $contribution,
            ]
        );

        $segmentEvent = new SegmentEvent(
            new Pid('sgv0000001'),
            $version,
            $segment,
            new Synopses('Segment Event synopses'),
            'Segment Event title'
        );

        $mapper = new FindByPidVersionMapper();
        $apsObject = $mapper->getApsObject($version, [], [$segmentEvent], []);

        $expectedSegmentEvents = [
            (object) [
                'title' => 'Segment Event title',
                'pid' => 'sgv0000001',
                'short_synopsis' => 'Segment Event synopses',
                'medium_synopsis' => '',
                'long_synopsis' => '',
                'version_offset' => null,
                'position' => null,
                'is_chapter' => false,
                'has_snippet' => false,
                'segment' => (object) [
                    'type' => 'Music',
                    'pid' => 'sg0000001',
                    'duration' => 180,
                    'primary_contributor' => (object) [
                        'pid' => 'p02z9mdz',
                        'name' => 'Benjamin Fry',
                        'musicbrainz_gid' => null,
                    ],
                    'contributions' => [
                        (object) [
                            'pid' => 'p02z9mdz',
                            'name' => 'Benjamin Fry',
                            'role' => 'Presenter',
                            'musicbrainz_gid' => null,
                        ],
                    ],
                    'title' => 'Segment Title',
                    'short_synopsis' => 'Segment synopses',
                    'medium_synopsis' => null,
                    'long_synopsis' => null,
                    'artist' => 'Benjamin Fry',
                    'track_title' => 'Segment Title',
                    'track_number' => null,
                    'publisher' => null,
                    'record_label' => null,
                    'release_title' => null,
                    'record_id' => null,
                    'catalogue_number' => null,
                ],
            ],
        ];

        $this->assertObjectHasAttribute('segment_events', $apsObject);
        $this->assertEquals($expectedSegmentEvents, $apsObject->{'segment_events'});
    }

    public function testMappingSegmentEventsNoContribution()
    {
        $version = $this->createMock(Version::CLASS);
        $episode = $this->createMock(Episode::CLASS);
        $version->method('getProgrammeItem')->willReturn($episode);

        $segment = new Segment(
            1,
            new Pid('sg0000001'),
            'Music',
            new Synopses('Segment synopses'),
            22,
            'Segment Title',
            180,
            [] // Empty contributions
        );

        $segmentEvent = new SegmentEvent(
            new Pid('sgv0000001'),
            $version,
            $segment,
            new Synopses('Segment Event synopses'),
            'Segment Event title'
        );

        $mapper = new FindByPidVersionMapper();
        $apsObject = $mapper->getApsObject($version, [], [$segmentEvent], []);

        $expectedSegmentEvents = [
            (object) [
                'title' => 'Segment Event title',
                'pid' => 'sgv0000001',
                'short_synopsis' => 'Segment Event synopses',
                'medium_synopsis' => '',
                'long_synopsis' => '',
                'version_offset' => null,
                'position' => null,
                'is_chapter' => false,
                'has_snippet' => false,
                'segment' =>
                    (object) [
                        'type' => 'Music',
                        'pid' => 'sg0000001',
                        'duration' => 180,
                        'contributions' => [],
                        'title' => 'Segment Title',
                        'short_synopsis' => 'Segment synopses',
                        'medium_synopsis' => null,
                        'long_synopsis' => null,
                    ],
            ],
        ];

        $this->assertObjectHasAttribute('segment_events', $apsObject);
        $this->assertEquals($expectedSegmentEvents, $apsObject->{'segment_events'});
    }
}
