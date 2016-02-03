<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use DateTime;
use InvalidArgumentException;
use stdClass;

class FindByPidProgrammeMapper implements MapperInterface
{
    const ENTITY_NS = 'BBC\\ProgrammesPagesService\\Domain\\Entity\\';

    const MAPPER_LOOKUP_TABLE = [
        self::ENTITY_NS . 'Brand' => 'getBrandObject',
        self::ENTITY_NS . 'Clip' => 'getClipObject',
        self::ENTITY_NS . 'Episode' => 'getEpisodeObject',
        self::ENTITY_NS . 'Series' => 'getSeriesObject',
    ];

    public function getApsObject($entity)
    {
        $entityClass = get_class($entity);

        if (is_null($entity)) {
            return null;
        }

        if (!array_key_exists($entityClass, self::MAPPER_LOOKUP_TABLE)) {
            throw new InvalidArgumentException('Could not find mapper for entity "' . $entityClass . '"');
        }

        return $this->{self::MAPPER_LOOKUP_TABLE[$entityClass]}($entity);
    }

    private function getBrandObject(Brand $programme)
    {
        $output = $this->getBaseProgrammeContainerObject($programme);
        $output->type = 'brand';

        return $output;
    }

    private function getSeriesObject(Series $programme)
    {
        $output = $this->getBaseProgrammeContainerObject($programme);
        $output->type = 'series';

        return $output;
    }

    private function getEpisodeObject(Episode $programme)
    {
        $output = $this->getBaseProgrammeItemObject($programme);
        $output->type = 'episode';

        return $output;
    }

    private function getClipObject(Clip $programme)
    {
        $output = $this->getBaseProgrammeItemObject($programme);
        $output->type = 'clip';

        return $output;
    }

    private function getBaseProgrammeApsObject(Programme $programme)
    {
        $output = new stdClass();

        $output->pid = $programme->getPid();
        $output->title = $programme->getTitle();
        $output->parent = $this->getApsObject($programme->getParent());

        return $output;
    }

    private function getBaseProgrammeContainerObject(ProgrammeContainer $programme)
    {
        $output = $this->getBaseProgrammeApsObject($programme);
        return $output;
    }

    private function getBaseProgrammeItemObject(ProgrammeItem $programme)
    {
        $output = $this->getBaseProgrammeApsObject($programme);
        $output->availableUntil = $programme->getStreamableUntil() ? $programme->getStreamableUntil()->format(DateTime::ISO8601) : null;

        return $output;
    }
}
