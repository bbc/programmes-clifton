<?php

namespace BBC\CliftonBundle\ApsMapper\Traits;

use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use InvalidArgumentException;

trait ProgrammeUtilitiesTrait
{

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

    protected function formatDateTime(\DateTimeImmutable $dateTimeImmutable): string
    {
        $dateTimeImmutable = $dateTimeImmutable->setTimezone(new \DateTimeZone('Europe/London'));
        if ($dateTimeImmutable->getOffset()) {
            // 2002-10-19T21:00:00+01:00
            return $dateTimeImmutable->format(DATE_ATOM);
        } else {
            // 2016-02-01T21:00:00Z
            return $dateTimeImmutable->format('Y-m-d\TH:i:s\Z');
        }
    }

    protected function getFirstBroadcastDate(Programme $programme)
    {
        $dateTime = $programme->getFirstBroadcastDate();
        return $dateTime ? $this->formatDateTime($dateTime) : null;
    }

    protected function getImageObject(Image $image)
    {
        // If the default image is returned by the Domain models, then Clifton
        // should not show any image model at all.
        if ($image->getPid() == 'p01tqv8z') {
            return null;
        }

        return (object) [
            'pid' => (string) $image->getPid(),
        ];
    }

    protected function getMediaType(Programme $programme)
    {
        if (!($programme instanceof ProgrammeItem)) {
            return null;
        }

        $mediaType = $programme->getMediaType();
        return $mediaType != MediaTypeEnum::UNKNOWN ? $mediaType : null;
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
}
