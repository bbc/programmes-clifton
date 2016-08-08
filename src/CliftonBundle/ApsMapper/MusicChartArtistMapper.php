<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\Traits\ProgrammeUtilitiesTrait;
use BBC\ProgrammesPagesService\Domain\Entity\Contributor;
use BBC\ProgrammesPagesService\Domain\Entity\MusicSegment;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
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
