<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\MusicChartArtistMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Contributor;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit_Framework_TestCase;

class MusicChartArtistMapperTest extends PHPUnit_Framework_TestCase
{
    public function testMappingArtistPlays()
    {
        $cMusicBrainz = '9c9f1380-2516-4fc9-a3e6-f9f61941d090';
        $cName = 'The Muse';
        $cSortName = 'Muse, The';

        $contributor = new Contributor(
            0,
            new Pid('cntrbp1d'),
            'person',
            $cName,
            $cSortName,
            $cName,
            $cName,
            $cMusicBrainz
        );

        $playsObject = (object) [
            'contributor' => $contributor,
            'plays' => 12,
        ];

        $expectedOutput = (object) [
            'gid' => $cMusicBrainz,
            'name' => $cName,
            'plays' => 12,
            'previous_plays' => 0,
        ];

        $mapper = new MusicChartArtistMapper();
        $this->assertEquals(
            $expectedOutput,
            $mapper->getApsObject($playsObject)
        );
    }
}
