<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\Network;
use stdClass;

class MusicChartNetworkMapper implements MapperInterface
{
    public function getApsObject(
        $network
    ): stdClass {
        /** @var Network $network */
        return (object) [
            'type' => $network->getMedium(),
            'key' => $network->getUrlKey(),
            'title' => $network->getName(),
        ];
    }
}
