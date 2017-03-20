<?php

namespace BBC\CliftonBundle\ApsMapper\Traits;

use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\Entity\MusicSegment;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;

trait SegmentUtilitiesTrait
{
    public function mapSegment(Segment $segment, array $segmentEvents, string $overrideType = null)
    {
        $contributions = $segment->getContributions();

        $output = [
            'pid' => (string) $segment->getPid(),
            'type' => is_null($overrideType) ? $this->mapSegmentType($segment->getType()) : $overrideType,
            'duration' => $segment->getDuration(),
            'title' => $this->mapSegmentTitle($segment->getTitle()),
            'short_synopsis' => $segment->getSynopses()->getShortSynopsis(),
            'medium_synopsis' => $segment->getSynopses()->getMediumSynopsis(),
            'long_synopsis' => $segment->getSynopses()->getLongSynopsis(),
            'segment_events' => $segmentEvents,
            'track_title' => $segment->getTitle(),
            'primary_contributor' => count($contributions) ? $this->mapSegmentPrimaryContributor($contributions[0]) : null,
            'contributions' => array_map([$this, 'mapSegmentContribution'], $contributions),
            'release_title' => $segment instanceof MusicSegment ? $segment->getReleaseTitle() : null,
            'catalogue_number' => $segment instanceof MusicSegment ? $segment->getCatalogueNumber() : null,
            'record_label' => $segment instanceof MusicSegment ? $segment->getRecordLabel() : null,
            'publisher' => $segment instanceof MusicSegment ? $segment->getPublisher() : null,
            'track_number' => $segment instanceof MusicSegment ? $segment->getTrackNumber() : null,
            'has_snippet' => false,
            'isrc' => null,
        ];

        if (is_null($output['primary_contributor'])) {
            unset($output['primary_contributor']);
        }

        return (object) $output;
    }

    public function mapSegmentTitle(string $title = null)
    {
        return (is_null($title) || $title === '') ? "Untitled" : $title;
    }


    public function mapSegmentPrimaryContributor(Contribution $contribution)
    {
        $output = [
            'pid' => (string) $contribution->getContributor()->getPid(),
            'name' => $contribution->getContributor()->getName(),
            'sort_name' => $contribution->getContributor()->getSortName(),
            'musicbrainz_gid' => $musicBrainzId = $contribution->getContributor()->getMusicBrainzId(),
        ];

        return (object) $output;
    }

    public function mapSegmentContribution(Contribution $contribution)
    {
        $output = [
            'pid' => (string) $contribution->getContributor()->getPid(),
            'name' => $contribution->getContributor()->getName(),
            'role' => $contribution->getCreditRole(),
            'musicbrainz_gid' => $contribution->getContributor()->getMusicBrainzId(),
        ];

        return (object) $output;
    }

    public function mapSegmentOwnership(Programme $programme)
    {
        $mb = $programme->getMasterBrand();
        if (!$mb) {
            return null;
        }

        $network = $mb->getNetwork();

        $output = [
            'type' => !empty($network->getMedium()) ? $network->getMedium() : null,
            'id' => (string) $network->getNid(),
            'key' => (string) $network->getUrlKey(),
            'title' => $network->getName(),
        ];

        // The values assigned here are technically wrong, as in APS world an
        // outlet is a mixture of a MasterBrand and a Service, whereas in the
        // ProgrammesDB world we have a Network as a denormed entity that is
        // a umbrella for Services. However we don't use the outlet for anything anyway.
        // The top-level 'service' is correct based upon the Network and that's what we care about.
        if ((string) $mb->getMid() != (string) $network->getNid()) {
            $output['outlet'] = (object) [
                'key' => $mb->getMid(),
                'title' => $mb->getName(),
            ];
        }

        return (object) ['service' => (object) $output];
    }

    public function mapSegmentType(string $type)
    {
        //APS only knows about the types 'classical', 'music', 'speech' (and 'deleted', but we don't use that).
        //Therefore, we have to map the values to the ones APS knows. If APS doesn't recognize the type, it outputs
        //an empty string
        if ($type == 'music' || $type == 'speech' || $type == 'classical') {
            return $type;
        } elseif ($type == 'chapter') {
            return 'speech';
        }

        return '';
    }
}
