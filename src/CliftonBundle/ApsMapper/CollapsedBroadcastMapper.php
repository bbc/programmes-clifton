<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\Traits\CollapsedBroadcastTrait;

class CollapsedBroadcastMapper implements MapperInterface
{
    use Traits\CollapsedBroadcastTrait;

    public function getApsObject($broadcast)
    {
        return $this->mapCollapsedBroadcast($broadcast);
    }
}
