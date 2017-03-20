<?php

namespace BBC\CliftonBundle\ApsMapper;

class CollapsedBroadcastMapper implements MapperInterface
{
    use Traits\CollapsedBroadcastTrait;

    public function getApsObject($broadcast): array
    {
        // This mapper could potentially return more collapsed broadcasts than it received.
        // PagesService definition of a collapsed broadcast differs from APS's. To make them equivalent
        // we have to split the collapsed broadcasts by network.
        return $this->mapCollapsedBroadcast($broadcast);
    }
}
