<?php

namespace BBC\CliftonBundle\ApsMapper\Traits;

use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use InvalidArgumentException;

trait ProgrammeUtilitiesTrait
{
    protected function getProgrammeType($entity): string
    {
        if ($entity instanceof Brand) {
            return 'brand';
        }
        if ($entity instanceof Series) {
            return 'series';
        }
        if ($entity instanceof Episode) {
            return 'episode';
        }
        if ($entity instanceof Clip) {
            return 'clip';
        }

        throw new InvalidArgumentException('Could not find type for entity "' . get_class($entity) . '"');
    }

    protected function getProgrammeTitle(Programme $programme)
    {
        // Mimic a dumb bug in APS: If the Title is a numeric string, then APS
        // outputs the value as a number, rather than a string
        // e.g. http://open.live.bbc.co.uk/aps/programmes/b008hskr.json
        $title = $programme->getTitle();
        return is_numeric($title) ? (int) $title : $title;
    }
}
