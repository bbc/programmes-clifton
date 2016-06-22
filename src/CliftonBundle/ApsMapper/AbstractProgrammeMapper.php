<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use InvalidArgumentException;

abstract class AbstractProgrammeMapper implements MapperInterface
{
    abstract public function getApsObject($entity);

    protected function assertIsProgramme($item)
    {
        if (!($item instanceof Programme)) {
            throw new InvalidArgumentException(sprintf(
                'Entity should be an instance of "%s". Got "%s"',
                'BBC\\ProgrammesPagesService\\Domain\\Entity\\Programme',
                (is_object($item) ? get_class($item) : gettype($item))
            ));
        }
    }

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

    protected function getMediaType(Programme $programme)
    {
        if (!($programme instanceof ProgrammeItem)) {
            return null;
        }

        $mediaType = $programme->getMediaType();
        return $mediaType != MediaTypeEnum::UNKNOWN ? $mediaType : null;
    }

    protected function getImageObject(Image $image)
    {
        // If the default image is returned by the Domain models, then Clifton
        // should not show any image model at all.
        if ($image->getPid() == 'p01tqv8z') {
            return null;
        }

        return (object) [
            'pid' => $image->getPid(),
        ];
    }

    protected function getFirstBroadcastDate(Programme $programme)
    {
        // Previously first_broadcast_date was based upon broadcasts of
        // episodes. As that info is expensive to calculate and we don't need it
        // elsewhere we're now going to base this off ReleaseDate which should
        // be good enough
        if (!($programme instanceof ProgrammeItem) || is_null($programme->getReleaseDate())) {
            return null;
        }

        // ReleaseDate is a PartialDate, so set to the first day/month if they were zeroes
        list($year, $month, $day) = explode('-', (string) $programme->getReleaseDate());
        $iso8601Date = sprintf(
            '%s-%s-%sT12:00:00Z',
            $year,
            $this->validDatePoint($month),
            $this->validDatePoint($day)
        );
        return $iso8601Date;
    }

    private function validDatePoint(int $point)
    {
        $point = $point ?: 1;
        return str_pad($point, '2', '0', STR_PAD_LEFT);
    }
}
