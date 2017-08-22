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

    protected function formatDateTime(\DateTimeImmutable $dateTimeImmutable = null): ?string
    {
        if (!$dateTimeImmutable) {
            return null;
        }

        $dateTimeImmutable = $dateTimeImmutable->setTimezone(new \DateTimeZone('Europe/London'));
        if ($dateTimeImmutable->getOffset()) {
            // 2002-10-19T21:00:00+01:00
            return $dateTimeImmutable->format(DATE_ATOM);
        } else {
            // 2016-02-01T21:00:00Z
            return $dateTimeImmutable->format('Y-m-d\TH:i:s\Z');
        }
    }

    protected function formatDate(\DateTimeImmutable $dateTimeImmutable): string
    {
        $dateTimeImmutable = $dateTimeImmutable->setTimezone(new \DateTimeZone('Europe/London'));
        return $dateTimeImmutable->format('Y-m-d');
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

    protected function getDisplayTitle(Programme $programme)
    {
        // Nasty but copying logic from:
        // https://repo.dev.bbc.co.uk/services/aps/trunk/lib/Helpers/Application.pm

        $titles = [];
        if ($this->isContainer($programme)) {
            $titles[] = $this->getProgrammeTitle($programme->getTitle());
        } else {
            foreach ($this->getHierarchy($programme) as $entity) {
                if ($this->isContainer($entity)) {
                    $titles[] = $entity->getTitle();
                }
            }
            $titles[] = $this->getProgrammeTitle($programme->getTitle());
        }

        return (object) [
            'title'    => array_shift($titles),
            'subtitle' => implode(', ', $titles),
        ];
    }

    protected function isContainer(Programme $programme): bool
    {
        return in_array($this->getProgrammeType($programme), ['brand', 'series']);
    }

    protected function getHierarchy(Programme $programme): array
    {
        $hierarchy = [$programme];
        while ($hierarchy[0]->getParent()) {
            array_unshift($hierarchy, $hierarchy[0]->getParent());
        }

        return $hierarchy;
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

    protected function getProgrammeTitle(string $title)
    {
        return ($title === '') ? "Untitled" : $title;
    }

    protected function getProgrammeOwnership(Programme $programme)
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

        // This is technically wrong, as in APS world an outlet is a mixture of
        // a MasterBrand and a Service, whereas in the ProgrammesDB world we
        // have a Network as a denormed entity that is a umberella for Services.
        // As such we don't know the services key, or shortName. However we
        // don't use the outlet for anything anyway. The top-level 'service' is
        // correct based upon the Network and that's what we care about.
        if ((string) $mb->getMid() != (string) $network->getNid()) {
            $output['outlet'] = (object) [
                'key' => '',
                'title' => $mb->getName(),
                'id' => (string) $mb->getMid(),
            ];
        }

        return (object) ['service' => (object) $output];
    }
}
