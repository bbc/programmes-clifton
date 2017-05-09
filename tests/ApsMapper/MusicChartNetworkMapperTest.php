<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\MusicChartNetworkMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Unfetched\UnfetchedImage;
use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\Nid;
use PHPUnit\Framework\TestCase;

class MusicChartNetworkMapperTest extends TestCase
{
    public function testMappingArtistPlays()
    {
        $urlKey = 'radio2';
        $title = 'Radio 2';

        $network = new Network(
            new Nid('bbc_radio_two'),
            $title,
            new UnfetchedImage(),
            $urlKey,
            'National Radio',
            NetworkMediumEnum::RADIO
        );

        $expectedOutput = (object) [
            'type' => NetworkMediumEnum::RADIO,
            'key' => $urlKey,
            'title' => $title,
        ];

        $mapper = new MusicChartNetworkMapper();
        $this->assertEquals(
            $expectedOutput,
            $mapper->getApsObject($network)
        );
    }
}
