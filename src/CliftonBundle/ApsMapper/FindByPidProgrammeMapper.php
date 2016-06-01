<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use DateTime;
use stdClass;

class FindByPidProgrammeMapper extends AbstractProgrammeMapper
{
    public function getApsObject($programme, $relatedLinks = [], $peers = [], $versions = []): stdClass
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
            'short_synopsis' => $programme->getShortSynopsis(),
            'medium_synopsis' => $programme->getShortSynopsis(), // We never use medium synopsis on the page
            'long_synopsis' => $programme->getLongestSynopsis(),
            'first_broadcast_date' => $this->getFirstBroadcastDate($programme),
            'display_title' => $this->getDisplayTitle($programme),
        ];

        // Ownership is only added if it is present
        $ownership = $this->getOwnership($programme);
        if ($ownership) {
            $output['ownership'] = $this->getOwnership($programme);
        }

        // Parents and Peers are only added to items with parents
        if ($programme->getParent()) {
            // TODO the parent object is different from the main object
            $output['parent'] = $this->getParent($programme->getParent());

            // Peers are only added to items that are not TLEOs
            // (i.e. things that have a parent)
            $output['peers'] = (object) [
                'previous' => $this->getPeer($peers['previous'] ?? null),
                'next' => $this->getPeer($peers['next'] ?? null),
            ];
        }

        // Versions are only added to ProgrammeItems
        if ($programme instanceof ProgrammeItem) {
            $output['versions'] = $this->getVersions($versions); //TODO
        }

        $output['links'] = $this->getRelatedLinks($relatedLinks); // TODO

        $output['supporting_content_items'] = []; // Not used anymore
        $output['categories'] = $this->getCategories($programme);

        return (object) $output;
    }

    private function getParent(Programme $programme)
    {

        $output = [
            'type' => $this->getProgrammeType($programme),
            'pid' => (string) $programme->getPid(),
            'title' => $programme->getTitle(),
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
            // TODO the parent object is different from the main object
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
            'type' => $network->getType(),
            'id' => $network->getNid(),
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
                'id' => $mb->getMid(),
            ];
        }

        return (object) ['service' => (object) $output];
    }

    private function getPeer($peer)
    {
        // TODO
        return null;
    }

    private function getVersions($versions)
    {
        // TODO
        return [];
    }

    private function getRelatedLinks($relatedLinks)
    {
        // TODO
        return [];
    }

    private function getCategories(Programme $programme)
    {
        $categories = $programme->getGenres() + $programme->getFormats();
        return [];
    }
}
