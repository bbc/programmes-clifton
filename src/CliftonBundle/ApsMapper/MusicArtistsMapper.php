<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\Traits\ProgrammeUtilitiesTrait;
use BBC\ProgrammesPagesService\Domain\Entity\Contributor;
use BBC\ProgrammesPagesService\Domain\Entity\MusicSegment;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use stdClass;

class MusicArtistsMapper implements MapperInterface
{
    use ProgrammeUtilitiesTrait;

    public function getApsObject($artist, $segmentEvents = []): stdClass
    {
        /** @var Contributor $artist */
        $musicBrainzId = $artist->getMusicBrainzId();
        $name = $artist->getName();
        $sortName = $name;

        return (object) [
            'gid' => $musicBrainzId,
            'name' => $name,
            'sort_name' => $sortName,
            'tleos_played_on' => [],
            'brands_played_on' => [],
            'services_played_on' => [],
            'latest_segment_events' => array_map(
                [$this, 'mapSegmentEvent'],
                $segmentEvents
            ),
        ];
    }

    private function mapSegmentEvent(SegmentEvent $segmentEvent): stdClass
    {
        return (object) [
            'pid' => (string) $segmentEvent->getPid(),
            'segment' => $this->mapSegment(
                $segmentEvent->getSegment()
            ),
            'version' => $this->mapVersion(
                $segmentEvent->getVersion()
            ),
            'episode' => $this->mapEpisode(
                $segmentEvent->getVersion()->getProgrammeItem()
            ),
            'tleo' => $this->mapTleo(
                $segmentEvent->getVersion()->getProgrammeItem()
            ),
        ];
    }

    private function mapSegment(Segment $segment): stdClass
    {
        $segmentData = [
            'pid' => (string) $segment->getPid(),
            'type' => 'SpeechSegment',
        ];

        if ($segment instanceof MusicSegment) {
            $segmentData['type'] = 'MusicSegment';
            $segmentData['track_title'] = $segment->getTitle();
            $segmentData['duration'] = $segment->getDuration();
            $segmentData['isrc'] = null;
            $segmentData['has_snippet'] = 'true';
        }
        return (object) $segmentData;
    }

    private function mapVersion(Version $version): stdClass
    {
        return (object) ['pid' => (string) $version->getPid()];
    }

    private function mapEpisode(ProgrammeItem $episode): stdClass
    {
        return (object) [
            'pid' => (string) $episode->getPid(),
            'title' => (string) $episode->getTitle(),
            'short_synopsis' => (string) $episode->getShortSynopsis(),
        ];
    }

    private function mapTleo(ProgrammeItem $episode): stdClass
    {
        $tleo = $episode->getTleo();

        $network = $tleo->getNetwork();
        $serviceKey = $network ? $network->getUrlKey() : '';

        return (object) [
            'pid' => (string) $tleo->getPid(),
            'type' => ucfirst($this->getProgrammeType($tleo)),
            'service_key' => $serviceKey,
            'title' => $tleo->getTitle(),
            'short_synopsis' => $tleo->getShortSynopsis(),
        ];
    }
}
