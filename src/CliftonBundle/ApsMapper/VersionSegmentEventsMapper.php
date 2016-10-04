<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use InvalidArgumentException;
use stdClass;

class VersionSegmentEventsMapper implements MapperInterface
{
    use Traits\VersionUtilitiesTrait;

    public function getApsObject($segmentEvent) : stdClass
    {
        /** @var SegmentEvent $segmentEvent */
        $this->assertIsSegmentEvent($segmentEvent);
        return $this->mapVersionSegmentEvent($segmentEvent);
    }

    private function assertIsSegmentEvent($item)
    {
        if (!($item instanceof SegmentEvent)) {
            throw new InvalidArgumentException(sprintf(
                'Entity should be an instance of "%s". Got "%s"',
                'BBC\\ProgrammesPagesService\\Domain\\Entity\\SegmentEvent',
                (is_object($item) ? get_class($item) : gettype($item))
            ));
        }
    }
}
