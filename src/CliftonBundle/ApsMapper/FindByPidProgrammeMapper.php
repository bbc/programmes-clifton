<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\RelatedLink;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use DateTime;
use stdClass;

class FindByPidProgrammeMapper extends AbstractProgrammeMapper
{
    public function getApsObject($programme, $relatedLinks = [], $nextSibling = null, $previousSibling = null, $versions = []): stdClass
    {
        $this->assertIsProgramme($programme);

        $output = [
            'type' => $this->getProgrammeType($programme),
            'pid' => (string) $programme->getPid(),
            'expected_child_count' => ($programme instanceof ProgrammeContainer) ? $programme->getExpectedChildCount() : null,
            'position' => $programme->getPosition(),
            'image' => $this->getImageObject($programme->getImage()),
            'media_type' => $this->getMediaType($programme),
            'title' => $programme->getTitle(),
            'short_synopsis' => $this->nullableSynopsis($programme->getSynopses()->getShortSynopsis()),
            'medium_synopsis' => $this->nullableSynopsis($programme->getSynopses()->getMediumSynopsis()),
            'long_synopsis' => $this->nullableSynopsis($programme->getSynopses()->getLongSynopsis()),
            'first_broadcast_date' => $this->getFirstBroadcastDate($programme),
            'display_title' => $this->getDisplayTitle($programme),
        ];

        // If Image is null then remove it from the feed
        if (is_null($output['image'])) {
            unset($output['image']);
        }

        // Ownership is only added if it is present
        $ownership = $this->getOwnership($programme);
        if ($ownership) {
            $output['ownership'] = $this->getOwnership($programme);
        }

        // Parents and Peers are only added to items with parents
        if ($programme->getParent()) {
            $output['parent'] = $this->getParent($programme->getParent());

            // Peers are only added to items that are not TLEOs
            // (i.e. things that have a parent)
            $output['peers'] = (object) [
                'previous' => $previousSibling ? $this->getPeer($previousSibling) : null,
                'next' => $nextSibling ? $this->getPeer($nextSibling) : null,
            ];
        }

        // Versions are only added to ProgrammeItems
        if ($programme instanceof ProgrammeItem) {
            $output['versions'] = array_map([$this, 'getVersion'], $versions);
        }

        $output['links'] = array_map([$this, 'getRelatedLink'], $relatedLinks);

        $output['supporting_content_items'] = []; // Not used anymore

        // Categories are sorted by their URL Key
        $categories = array_merge($programme->getGenres(), $programme->getFormats());
        usort($categories, function ($a, $b) {
            return $a->getUrlKey() <=> $b->getUrlKey();
        });

        $output['categories'] = array_map([$this, 'getCategory'], $categories);

        return (object) $output;
    }

    private function nullableSynopsis(string $synopsis)
    {
        return $synopsis === '' ? null : $synopsis;
    }

    private function getParent(Programme $programme)
    {
        $output = [
            'type' => $this->getProgrammeType($programme),
            'pid' => (string) $programme->getPid(),
            'title' => $programme->getTitle(),
            // Only synopses at the top level coerce empty strings to null
            'short_synopsis' => $programme->getShortSynopsis(),
            'position' => $programme->getPosition(),
            'image' => $this->getImageObject($programme->getImage()),
            'expected_child_count' => ($programme instanceof ProgrammeContainer) ? $programme->getExpectedChildCount() : null,
            'first_broadcast_date' => $this->getFirstBroadcastDate($programme),
        ];

        // Ownership is only added if it is present
        $ownership = $this->getOwnership($programme);
        if ($ownership) {
            $output['ownership'] = $this->getOwnership($programme);
        }

        // Parents and Peers are only added to items with parents
        if ($programme->getParent()) {
            $output['parent'] = $this->getParent($programme->getParent());
        }

        return (object) ['programme' => (object) $output];
    }

    private function getDisplayTitle(Programme $programme)
    {
        $hierarchy = [$programme];
        while ($hierarchy[0]->getParent()) {
            array_unshift($hierarchy, $hierarchy[0]->getParent());
        }

        // Subtitles are everything but the TLEO title
        $subtitlesSet = array_slice($hierarchy, 1);

        // If the programme is a clip whose parent is an Episode, the parent
        // episode should be removed from the subtitles too
        if ($programme instanceof Clip && $programme->getParent() instanceof Episode) {
            // Last item is the current item, last but one item is the parent
            $offset = count($subtitlesSet) - 2;
            if (isset($subtitlesSet[$offset])) {
                unset($subtitlesSet[$offset]);
            }
        }

        $subtitles = [];
        foreach ($subtitlesSet as $item) {
            $subtitles[] = $item->getTitle();
        }

        return (object) [
            'title' => $hierarchy[0]->getTitle(),
            'subtitle' => implode(', ', $subtitles),
        ];
    }

    private function getOwnership(Programme $programme)
    {
        $mb = $programme->getMasterBrand();
        if (!$mb) {
            return null;
        }

        $network = $mb->getNetwork();

        $output = [
            'type' => $network->getMedium(),
            'id' => (string) $network->getNid(),
            'key' => $network->getUrlKey(),
            'title' => $network->getName(),
        ];

        // This is technically wrong, as in APS world an outlet is a mixture of
        // a MasterBrand and a Service, whereas in the ProgrammesDB world we
        // have a Network as a denormed entity that is a umberella for Services.
        // As such we don't know the services key, or shortName. However we
        // don't use the outlet for anything anyway. The top-level 'service' is
        // correct based upon the Network and that's what we care about.
        if ((string) $mb->getMid() != (string) $network->getNid()) {
            $output['outlet'] = (object) [
                'key' => null,
                'title' => $mb->getName(),
                'id' => (string) $mb->getMid(),
            ];
        }

        return (object) ['service' => (object) $output];
    }

    private function getPeer(Programme $programme)
    {
        return (object) [
            'type' => $this->getProgrammeType($programme),
            'pid' => (string) $programme->getPid(),
            'title' => $programme->getTitle(),
            'first_broadcast_date' => $this->getFirstBroadcastDate($programme),
            'position' => $programme->getPosition(),
            'media_type' => $this->getMediaType($programme),
        ];
    }

    private function getVersion(Version $version)
    {
        $isCanonical = 0;
        foreach ($version->getVersionTypes() as $vt) {
            if ($vt->getType() == 'Original') {
                $isCanonical = 1;
                break;
            }
        }

        return (object) [
            'canonical' => $isCanonical,
            'pid' => $version->getPid(),
            'duration' => $version->getDuration(),
            'types' => array_map(function ($vt) {
                return $vt->getName();
            }, $version->getVersionTypes()),
        ];
    }

    private function getRelatedLink(RelatedLink $relatedLink)
    {
        return (object) [
            'type' => $relatedLink->getType(),
            'title' => $relatedLink->getTitle(),
            'url' => $relatedLink->getUri(),
        ];
    }

    private function getCategory($category, $showNarrower = true)
    {
        $output = [
            'type' => ($category instanceof Genre ? 'genre' : 'format'),
            'id' => $category->getId(),
            'key' => $category->getUrlKey(),
            'title' => $category->getTitle(),
        ];

        if ($showNarrower) {
            $output['narrower'] = [];
        }

        if ($category instanceof Genre) {
            $parent = $category->getParent();
            if ($parent) {
                $output['broader'] = (object) [
                    'category' => $this->getCategory($parent, false),
                ];
            } else {
                $output['broader'] = (object) [];
            }
        }

        $output['has_topic_page'] = false;
        $output['sameAs'] = null;

        return (object) $output;
    }
}
