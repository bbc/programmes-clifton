<?php

namespace BBC\CliftonBundle\ApsMapper;

use stdClass;

class MusicChartArtistMapper implements MapperInterface
{
    public function getApsObject(
        $artistPlays
    ): stdClass {
        $artist = $artistPlays->contributor;
        $plays = $artistPlays->plays;
        return (object) [
            'gid' => $artist->getMusicBrainzId(),
            'name' => $artist->getName(),
            'plays' => $plays,
            'previous_plays' => 0,
        ];
    }
}
