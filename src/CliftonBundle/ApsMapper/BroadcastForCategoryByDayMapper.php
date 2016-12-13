<?php

namespace BBC\CliftonBundle\ApsMapper;

class BroadcastForCategoryByDayMapper implements MapperInterface
{
    use Traits\CollapsedBroadcastTrait;

    public function getApsObject($broadcasts)
    {
        return (object) ['broadcasts' => array_map([$this, 'mapCollapsedBroadcast'], $broadcasts)];
    }
}
