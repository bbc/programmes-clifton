<?php

namespace BBC\CliftonBundle\ApsMapper;

class CollapsedBroadcastMapper implements MapperInterface
{
    use Traits\CollapsedBroadcastTrait;

    public function getApsObject($broadcast)
    {
        return $this->mapCollapsedBroadcast($broadcast);
    }
}
