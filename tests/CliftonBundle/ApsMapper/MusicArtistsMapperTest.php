<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\MusicArtistsMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\Contributor;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\MasterBrand;
use BBC\ProgrammesPagesService\Domain\Entity\MusicSegment;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use BBC\ProgrammesPagesService\Domain\Entity\Unfetched\UnfetchedImage;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use PHPUnit_Framework_TestCase;

class MusicArtistsMapperTest extends PHPUnit_Framework_TestCase
{
    public function testMappingSegmentEvents()
    {
        $cMusicBrainz = '9c9f1380-2516-4fc9-a3e6-f9f61941d090';
        $cName = 'The Muse';
        $cSortName = 'Muse, The';

        $contrbutor = new Contributor(
            0,
            new Pid('cntrbp1d'),
            'person',
            $cName,
            $cSortName,
            $cName,
            $cName,
            $cMusicBrainz
        );

        $sePid1 = 'sgmntvt1';
        $sPid1 = 'sgmnt001';
        $sTitle1 = 'Segment title';

        $sePid2 = 'sgmntvt2';
        $sPid2 = 'sgmnt002';
        $sTitle2 = 'Segment title 2';
        $duration = 100;

        $segment1 = new Segment(
            0,
            new Pid($sPid1),
            'speech',
            new Synopses('', '', ''),
            $sTitle1
        );

        $segmentEvent1 = new SegmentEvent(
            new Pid($sePid1),
            $this->getExampleVersion(),
            $segment1,
            new Synopses('', '', '')
        );

        $segment2 = new MusicSegment(
            0,
            new Pid($sPid2),
            'music',
            new Synopses('A short synopsis', '', ''),
            $sTitle2,
            $duration
        );

        $segmentEvent2 = new SegmentEvent(
            new Pid($sePid2),
            $this->getExampleVersion(),
            $segment2,
            new Synopses('', '', ''),
            'A segment event title'
        );

        $expectedOutput = (object) [
            'gid' => $cMusicBrainz,
            'name' => $cName,
            'sort_name' => $cSortName,
            'tleos_played_on' => [],
            'brands_played_on' => [],
            'services_played_on' => [],
            'latest_segment_events' => [
                $this->getExpectedSegmentEvent(
                    $sePid1,
                    $sPid1,
                    'SpeechSegment',
                    null,
                    $sTitle1
                ),
                $this->getExpectedSegmentEvent(
                    $sePid2,
                    $sPid2,
                    'MusicSegment',
                    $duration,
                    $sTitle2,
                    'A segment event title',
                    'A short synopsis'
                ),
            ],
        ];

        $mapper = new MusicArtistsMapper();
        $this->assertEquals(
            $expectedOutput,
            $mapper->getApsObject($contrbutor, [$segmentEvent1, $segmentEvent2])
        );
    }

    private function getExpectedSegmentEvent(
        $sePid,
        $sPid,
        $type,
        $duration = null,
        $title = null,
        $segmentEventTitle = null,
        $segmentShortSynopsis = null
    ) {
        $segmentEvent = (object) [
            'pid' => $sePid,
            'title' => $segmentEventTitle,
            'segment' =>  (object) [
                'pid' => $sPid,
                'type' => $type,
            ],
            'version' =>  (object) [
                'pid' => 'vrsnpd01',
            ],
            'episode' =>  (object) [
                'pid' => 'prg1tm01',
                'title' => 'Episode title',
                'short_synopsis' => 'Short Synopsis',
            ],
            'tleo' =>  (object) [
                'pid' => 'brndpd01',
                'type' => 'Brand',
                'service_key' => 'radio2',
                'title' => 'The Brand',
                'short_synopsis' => 'Brand Synopsis',
            ],
        ];

        if (!$segmentEventTitle) {
            unset($segmentEvent->title);
        }

        if ($type == 'MusicSegment') {
            $segmentEvent->segment->track_title = $title;
            $segmentEvent->segment->duration = $duration;
            $segmentEvent->segment->isrc = null;
            $segmentEvent->segment->has_snippet = 'true';
        }

        if ($segmentShortSynopsis) {
            $segmentEvent->segment->short_synopsis = $segmentShortSynopsis;
        }

        return $segmentEvent;
    }

    private function getExampleVersion()
    {
        $eTitle = 'Episode title';

        $network = $this->createMock(Network::CLASS);
        $network->method('getUrlKey')->willReturn('radio2');

        $masterBrand = $this->createMock(MasterBrand::CLASS);
        $masterBrand->method('getNetwork')->willReturn($network);

        // Needs full brand, as it needs to match the instance type
        $brand = new Brand(
            [0],
            new Pid('brndpd01'),
            'The Brand',
            '',
            new Synopses('Brand Synopsis', '', ''),
            new UnfetchedImage(),
            0,
            0,
            false,
            false,
            false,
            0,
            0,
            0,
            0,
            0,
            false,
            null,
            null,
            $masterBrand
        );

        $episode = new Episode(
            [0],
            new Pid('prg1tm01'),
            $eTitle,
            $eTitle,
            new Synopses('Short Synopsis', '', ''),
            new UnfetchedImage(),
            0,
            0,
            false,
            false,
            false,
            'audio',
            0,
            0,
            0,
            0,
            $brand
        );

        return new Version(
            0,
            new Pid('vrsnpd01'),
            $episode,
            false,
            false,
            0
        );
    }
}
