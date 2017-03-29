<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\VersionType;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\RelatedLink;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use stdClass;

class FindByPidProgrammeMapper implements MapperInterface
{
    use Traits\ProgrammeUtilitiesTrait;

    public function getApsObject($programme, $relatedLinks = [], $nextSibling = null, $previousSibling = null, $versions = []): stdClass
    {
        /** @var ProgrammeContainer $programme */
        $this->assertIsProgramme($programme);
        $output = [
            'type' => $this->getProgrammeType($programme),
            'pid' => (string) $programme->getPid(),
            'expected_child_count' => ($programme instanceof ProgrammeContainer) ? $programme->getExpectedChildCount() : null,
            'position' => $programme->getPosition(),
            'image' => $this->getImageObject($programme->getImage()),
            'media_type' => $this->getMediaType($programme),
            'title' => $this->getProgrammeTitle($programme->getTitle()),
            'short_synopsis' => $this->nullableSynopsis($programme->getSynopses()->getShortSynopsis()),
            'medium_synopsis' => $this->nullableSynopsis($programme->getSynopses()->getMediumSynopsis()),
            'long_synopsis' => $this->nullableSynopsis($programme->getSynopses()->getLongSynopsis()),
            'first_broadcast_date' => $this->getFirstBroadcastDate($programme),
            'display_title' => $this->getDisplayTitle($programme),
        ];

        // If the programme is a container add the descendent episodes
        if (($programme instanceof ProgrammeContainer)) {
            $output['aggregated_episode_count'] = $programme->getAggregatedEpisodesCount();
        }

        // If Image is null then remove it from the feed
        if (is_null($output['image'])) {
            unset($output['image']);
        }

        // Ownership is only added if it is present
        $ownership = $this->getProgrammeOwnership($programme);
        if ($ownership) {
            $output['ownership'] = $this->getProgrammeOwnership($programme);
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
            'title' => $this->getProgrammeTitle($programme->getTitle()),
            // Only synopses at the top level coerce empty strings to null
            'short_synopsis' => $programme->getShortSynopsis(),
            'position' => $programme->getPosition(),
            'image' => $this->getImageObject($programme->getImage()),
            'expected_child_count' => ($programme instanceof ProgrammeContainer) ? $programme->getExpectedChildCount() : null,
            'first_broadcast_date' => $this->getFirstBroadcastDate($programme),
        ];

        // If the programme is a container add the descendent episodes
        if (($programme instanceof ProgrammeContainer)) {
            $output['aggregated_episode_count'] = $programme->getAggregatedEpisodesCount();
        }

        // If Image is null then remove it from the feed
        if (is_null($output['image'])) {
            unset($output['image']);
        }

        // Ownership is only added if it is present
        $ownership = $this->getProgrammeOwnership($programme);
        if ($ownership) {
            $output['ownership'] = $this->getProgrammeOwnership($programme);
        }

        // Parents and Peers are only added to items with parents
        if ($programme->getParent()) {
            $output['parent'] = $this->getParent($programme->getParent());
        }

        return (object) ['programme' => (object) $output];
    }

    private function getPeer(Programme $programme)
    {
        return (object) [
            'type' => $this->getProgrammeType($programme),
            'pid' => (string) $programme->getPid(),
            'title' => $this->getProgrammeTitle($programme->getTitle()),
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
            'types' => array_map(function (VersionType $vt) {
                return $vt->getName();
            }, $version->getVersionTypes()),
        ];
    }

    private function getRelatedLink(RelatedLink $relatedLink)
    {
        return (object) [
            'type' => $relatedLink->getType(),
            'title' => !empty($relatedLink->getTitle()) ? $relatedLink->getTitle() : null,
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

        $broader = [];
        if ($category instanceof Genre) {
            $parent = $category->getParent();
            if ($parent) {
                $broader = [
                    'category' => $this->getCategory($parent, false),
                ];
            }
        }

        $output['broader'] = (object) $broader;
        $output['has_topic_page'] = false;
        $output['sameAs'] = null;

        return (object) $output;
    }
}
