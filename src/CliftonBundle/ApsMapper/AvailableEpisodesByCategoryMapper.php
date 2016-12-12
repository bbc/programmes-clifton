<?php

namespace BBC\CliftonBundle\ApsMapper;

class AvailableEpisodesByCategoryMapper implements MapperInterface
{
    use Traits\EpisodeItemTrait;

    public function getApsObject($episode)
    {
        return (object) ['programme' => $this->mapEpisodeItem($episode, true)];
    }
}
