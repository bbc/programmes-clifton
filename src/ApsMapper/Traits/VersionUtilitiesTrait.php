<?php

namespace BBC\CliftonBundle\ApsMapper\Traits;

use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\MusicSegment;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;

trait VersionUtilitiesTrait
{
    use SegmentUtilitiesTrait;

    public function mapVersionSegmentEvent(SegmentEvent $segmentEvent)
    {
        $output = [
            'title' => $segmentEvent->getTitle(),
            'pid' => (string) $segmentEvent->getPid(),
            'short_synopsis' => $segmentEvent->getSynopses()->getShortSynopsis(),
            'medium_synopsis' => $segmentEvent->getSynopses()->getMediumSynopsis(),
            'long_synopsis' => $segmentEvent->getSynopses()->getLongSynopsis(),
            'version_offset' => $segmentEvent->getOffset(),
            'position' => $segmentEvent->getPosition(),
            'is_chapter' => $segmentEvent->isChapter(),
            'has_snippet' => false,
            'segment' => $this->mapVersionSegment($segmentEvent->getSegment()),
        ];

        return (object) $output;
    }

    public function mapVersionSegment(Segment $segment)
    {
        $output = [
            'type' => $segment->getType(),
            'pid' => (string) $segment->getPid(),
            'duration' => $segment->getDuration(),
        ];

        $contributions = $segment->getContributions();

        if ($segment instanceof MusicSegment) {
            /** @var MusicSegment $segment */

            if (!empty($contributions)) {
                $primaryContribution = $contributions[0];
                $output['primary_contributor'] = $this->mapVersionSegmentPrimaryContributor($primaryContribution);
                $output['artist'] = $primaryContribution->getContributor()->getName();
            } else {
                $output['artist'] = null;
            }

            $output['track_title'] = $this->mapSegmentTitle($segment->getTitle());
            $output['track_number'] = $segment->getTrackNumber();
            $output['publisher'] = $segment->getPublisher();
            $output['record_label'] = $segment->getRecordLabel();
            $output['release_title'] = $segment->getReleaseTitle();
            $output['record_id'] = $segment->getMusicRecordId();
            $output['catalogue_number'] = $segment->getCatalogueNumber();
        }

        $output['contributions'] = array_map([$this, 'mapSegmentContribution'], $contributions);
        $output['title'] = $this->mapSegmentTitle($segment->getTitle());
        $output['short_synopsis'] = $segment->getSynopses()->getShortSynopsis() ?: null;
        $output['medium_synopsis'] = $segment->getSynopses()->getMediumSynopsis() ?: null;
        $output['long_synopsis'] = $segment->getSynopses()->getLongSynopsis() ?: null;

        return (object) $output;
    }

    public function mapVersionSegmentPrimaryContributor(Contribution $contribution)
    {
        $output = [
            'pid' => (string) $contribution->getContributor()->getPid(),
            'musicbrainz_gid' => $contribution->getContributor()->getMusicBrainzId(),
            'name' => $contribution->getContributor()->getName(),
        ];

        return (object) $output;
    }
}
