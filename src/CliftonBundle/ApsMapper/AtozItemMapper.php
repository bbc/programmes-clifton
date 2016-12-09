<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use stdClass;
use BBC\ProgrammesPagesService\Domain\Entity\AtozTitle;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class AtozItemMapper implements MapperInterface
{
    use Traits\ProgrammeUtilitiesTrait;

    public function getApsObject($atoZTitle): stdClass
    {
        /** @var AtozTitle $atoZTitle */
        $output = [
            'title' => $atoZTitle->getTitle(),
            'letter' => $atoZTitle->getFirstLetter(),
            'programme' => $this->getProgramme($atoZTitle->getTitledEntity()),
        ];
        return (object) $output;
    }

    public function getProgramme(Programme $programme)
    {
        $output = [
            'type' => $this->getProgrammeType($programme),
            'pid' => (string) $programme->getPid(),
            'title' => $this->getProgrammeTitle($programme->getTitle()),
            'short_synopsis' => $programme->getShortSynopsis(),
            'image' => $this->getImageObject($programme->getImage()),
            'position' => $programme->getPosition(),
            'expected_child_count' => ($programme instanceof ProgrammeContainer) ? $programme->getExpectedChildCount() : null,
            'first_broadcast_date' => $this->getFirstBroadcastDate($programme),
            'is_available' => $programme->isStreamable(),
            'ownership' => $this->getProgrammeOwnership($programme),
        ];

        if (is_null($output['ownership'])) {
            unset($output['ownership']);
        }

        // If Image is null then remove it from the feed
        if (is_null($output['image'])) {
            unset($output['image']);
        }
        return (object) $output;
    }
}
