<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\MusicSegment;
use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use InvalidArgumentException;
use stdClass;

class FindByPidSegmentMapper implements MapperInterface
{
    use Traits\SegmentUtilitiesTrait;
    use Traits\ProgrammeUtilitiesTrait;

    public function getApsObject($segment, array $contributions = [], array $segmentEvents = []): stdClass
    {
        /** @var Segment $segment */
        $this->assertIsSegment($segment);

        $output = [
            'pid' => (string) $segment->getPid(),
            'type' => $this->getAsNumberOrString($segment->getType()),
            'duration' => $segment->getDuration(),
            'title' => $this->getAsNumberOrString($segment->getTitle()),
            'short_synopsis' => $this->getAsNumberOrString($segment->getSynopses()->getShortSynopsis()),
            'medium_synopsis' => $this->getAsNumberOrString($segment->getSynopses()->getMediumSynopsis()),
            'long_synopsis' => $this->getAsNumberOrString($segment->getSynopses()->getLongSynopsis()),
            'segment_events' => array_map([$this, 'getSegmentEvent'], $segmentEvents),
            'track_title' => $this->getAsNumberOrString($segment->getTitle()),
            'primary_contributor' => count($contributions) ? $this->getPrimaryContributor($contributions[0]) : null,
            'contributions' => array_map([$this, 'getContribution'], $contributions),
            'release_title' =>
                $segment instanceof MusicSegment ? $this->getAsNumberOrString($segment->getReleaseTitle()) : null,
            'catalogue_number' =>
                $segment instanceof MusicSegment ?
                    $this->getAsNumberOrString($segment->getCatalogueNumber()) :
                    null,
            'record_label' =>
                $segment instanceof MusicSegment ? $this->getAsNumberOrString($segment->getRecordLabel()) : null,
            'publisher' =>
                $segment instanceof MusicSegment ? $this->getAsNumberOrString($segment->getPublisher()) : null,
            'track_number' =>
                $segment instanceof MusicSegment ? $this->getAsNumberOrString($segment->getTrackNumber()) : null,
            'has_snippet' => false,
            'isrc' => null,
        ];

        if (is_null($output['primary_contributor'])) {
            unset($output['primary_contributor']);
        }

        return (object) $output;
    }

    private function assertIsSegment($item)
    {
        if (!($item instanceof Segment)) {
            throw new InvalidArgumentException(sprintf(
                'Entity should be an instance of "%s". Got "%s"',
                'BBC\\ProgrammesPagesService\\Domain\\Entity\\Segment',
                (is_object($item) ? get_class($item) : gettype($item))
            ));
        }
    }

    private function getPrimaryContributor(Contribution $contribution)
    {
        $output = [
            'pid' => (string) $contribution->getContributor()->getPid(),
            'name' => $this->getAsNumberOrString($contribution->getContributor()->getName()),
            'sort_name' => $this->getAsNumberOrString($contribution->getContributor()->getSortName()),
            'musicbrainz_gid' => $musicBrainzId = $contribution->getContributor()->getMusicBrainzId(),
        ];

        return (object) $output;
    }

    private function getContribution(Contribution $contribution)
    {
        $output = [
            'pid' => (string) $contribution->getContributor()->getPid(),
            'name' => $this->getAsNumberOrString($contribution->getContributor()->getName()),
            'role' => $this->getAsNumberOrString($contribution->getCreditRole()),
            'musicbrainz_gid' => $contribution->getContributor()->getMusicBrainzId(),
        ];

        return (object) $output;
    }

    private function getSegmentEvent(SegmentEvent $segmentEvent)
    {
        $output = [
            'pid' => (string) $segmentEvent->getPid(),
            'title' => $this->getAsNumberOrString($segmentEvent->getTitle()),
            'short_synopsis' => $this->getAsNumberOrString($segmentEvent->getSynopses()->getShortSynopsis()),
            'medium_synopsis' => $this->getAsNumberOrString($segmentEvent->getSynopses()->getMediumSynopsis()),
            'long_synopsis' => $this->getAsNumberOrString($segmentEvent->getSynopses()->getLongSynopsis()),
            'version_offset' => $segmentEvent->getOffset(),
            'position' => $segmentEvent->getPosition(),
            'is_chapter' => $segmentEvent->isChapter(),
            'version' => $this->getVersion($segmentEvent->getVersion()),
        ];

        return (object) $output;
    }

    private function getVersion(Version $version)
    {
        $output = [
            'pid' => (string) $version->getPid(),
            'duration' => $version->getDuration(),
            'programme' => $this->getParent($version->getProgrammeItem()),
        ];

        return (object) $output;
    }

    private function getParent(Programme $programme)
    {
        $output = [
            'type' => $this->getAsNumberOrString($this->getProgrammeType($programme)),
            'pid' => (string) $programme->getPid(),
            'title' => $this->getAsNumberOrString($programme->getTitle()),
            'image' => $this->getImageObject($programme->getImage()),
            'short_synopsis' => $this->getAsNumberOrString($programme->getShortSynopsis()),
            'media_type' => $this->getMediaType($programme),
        ];

        // If Image is null then remove it from the feed
        if (is_null($output['image'])) {
            unset($output['image']);
        }

        // Parents are only added to items with parents
        if ($programme->getParent()) {
            $output['parent'] = (object) ['programme' => $this->getParent($programme->getParent())];
        }

        if ($this->getOwnership($programme)) {
            $output['ownership'] = $this->getOwnership($programme);
        }

        return (object) $output;
    }

    private function getOwnership(Programme $programme)
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
}