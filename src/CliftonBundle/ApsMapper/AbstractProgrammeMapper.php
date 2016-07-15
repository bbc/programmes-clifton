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
use DateTimeZone;
use InvalidArgumentException;

abstract class AbstractProgrammeMapper implements MapperInterface
{
    use Traits\ProgrammeUtilitiesTrait;

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

    protected function nullableSynopsis(string $synopsis)
    {
        return $synopsis === '' ? null : $synopsis;
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
        $dateTime = $programme->getFirstBroadcastDate();
        return $dateTime ? $this->formatDateTime($dateTime) : null;
    }

    protected function formatDateTime(\DateTimeImmutable $dateTimeImmutable): string
    {
        $dateTimeImmutable = $dateTimeImmutable->setTimezone(new DateTimeZone('Europe/London'));
        if ($dateTimeImmutable->getOffset()) {
            // 2002-10-19T21:00:00+01:00
            return $dateTimeImmutable->format(DATE_ATOM);
        } else {
            // 2016-02-01T21:00:00Z
            return $dateTimeImmutable->format('Y-m-d\TH:i:s\Z');
        }
    }
}
