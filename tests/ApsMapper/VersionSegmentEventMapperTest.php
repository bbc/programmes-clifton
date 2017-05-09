<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\VersionSegmentEventsMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\Entity\Contributor;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\MusicSegment;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use PHPUnit\Framework\TestCase;

class VersionSegmentEventMapperTest extends TestCase
{
    public function testMappingVersionSegmentEventMapper()
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

        $segment = new Segment(
            1,
            new Pid('sg0000001'),
            'speech',
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

        $mapper = new VersionSegmentEventsMapper();
        $apsObject = $mapper->getApsObject($segmentEvent);

        $expectedSegmentEvents = (object) [
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
                'type' => 'speech',
                'pid' => 'sg0000001',
                'duration' => 180,
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
            ],
        ];

        $this->assertEquals($expectedSegmentEvents, $apsObject);
    }

    public function testMappingVerionSegmentEventWithMusicSegmentMapper()
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

        $mapper = new VersionSegmentEventsMapper();
        $apsObject = $mapper->getApsObject($segmentEvent);

        $expectedSegmentEvents = (object) [
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
        ];

        $this->assertEquals($expectedSegmentEvents, $apsObject);
    }
}
