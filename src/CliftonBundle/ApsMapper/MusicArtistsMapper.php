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
    use Traits\SegmentUtilitiesTrait;

    public function getApsObject($artist, $segmentEvents = []): stdClass
    {
        /** @var Contributor $artist */
        $musicBrainzId = $artist->getMusicBrainzId();
        $name = $artist->getName();
        $sortName = $artist->getSortName();

        return (object) [
            'gid' => $musicBrainzId,
            'name' => $name,
            'sort_name' => $sortName,
            'tleos_played_on' => [],
            'brands_played_on' => [],
            'services_played_on' => [],
            'latest_segment_events' => array_map(
                [$this, 'getSegmentEvent'],
                $segmentEvents
            ),
        ];
    }

    private function getSegmentEvent(SegmentEvent $segmentEvent): stdClass
    {
        $data = ['pid' => (string) $segmentEvent->getPid()];

        if ($segmentEvent->getTitle()) {
            $data['title'] = $segmentEvent->getTitle();
        }

        $data['segment'] = $this->getSegment(
            $segmentEvent->getSegment()
        );
        $data['version'] = $this->getVersion(
            $segmentEvent->getVersion()
        );
        $data['episode'] = $this->getEpisode(
            $segmentEvent->getVersion()->getProgrammeItem()
        );
        $data['tleo'] = $this->getTleo(
            $segmentEvent->getVersion()->getProgrammeItem()
        );

        return (object) $data;
    }

    private function getSegment(Segment $segment): stdClass
    {
        $segmentData = [
            'pid' => (string) $segment->getPid(),
            'type' => 'SpeechSegment',
        ];

        if ($segment instanceof MusicSegment) {
            $segmentData['type'] = 'MusicSegment';
            $segmentData['track_title'] = $this->mapSegmentTitle($segment->getTitle());
            $segmentData['duration'] = $segment->getDuration();
            $segmentData['isrc'] = null;
            $segmentData['has_snippet'] = 'true';
        }

        $shortSynopsis = $segment->getSynopses()->getShortSynopsis();
        if (!empty($shortSynopsis)) {
            $segmentData['short_synopsis'] = $segment->getSynopses()->getShortSynopsis();
        }

        return (object) $segmentData;
    }

    private function getVersion(Version $version): stdClass
    {
        return (object) ['pid' => (string) $version->getPid()];
    }

    private function getEpisode(ProgrammeItem $episode): stdClass
    {
        return (object) [
            'pid' => (string) $episode->getPid(),
            'title' => (string) $episode->getTitle(),
            'short_synopsis' => (string) $episode->getShortSynopsis(),
        ];
    }

    private function getTleo(ProgrammeItem $episode): stdClass
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
