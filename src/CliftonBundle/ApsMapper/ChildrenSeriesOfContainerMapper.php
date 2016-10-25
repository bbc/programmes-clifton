<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use stdClass;
use InvalidArgumentException;

class ChildrenSeriesOfContainerMapper implements MapperInterface
{
    use Traits\ProgrammeUtilitiesTrait;

    public function getApsObject($programme): stdClass
    {
        /** @var ProgrammeContainer $programme */
        $this->assertIsProgrammeContainer($programme);

        $output = [
            'type' => $this->getProgrammeType($programme),
            'pid' => (string) $programme->getPid(),
            'title' => $programme->getTitle(),
            'short_synopsis' => $programme->getShortSynopsis(),
            'image' => $this->getImageObject($programme->getImage()),
            'position' => $programme->getPosition(),
            'expected_child_count' => $programme->getExpectedChildCount(),
            'first_broadcast_date' => $this->getFirstBroadcastDate($programme),
        ];

        if (is_null($output['image'])) {
            unset($output['image']);
        }

        return (object) $output;
    }

    private function assertIsProgrammeContainer($item)
    {
        if (!($item instanceof ProgrammeContainer)) {
            throw new InvalidArgumentException(sprintf(
                'Entity should be an instance of "%s". Got "%s"',
                'BBC\\ProgrammesPagesService\\Domain\\Entity\\ProgrammeContainer',
                (is_object($item) ? get_class($item) : gettype($item))
            ));
        }
    }
}
