<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

/**
 * @covers BBC\CliftonBundle\Controller\FindByPidController
 */
class MusicArtistsControllerTest extends BaseWebTestCase
{
    public function testMusicArtistWithSegmentEvents()
    {
        $this->loadFixtures(['SegmentEventsForArtistsFixture']);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/music/artists/7746d775-9550-4360-b8d5-c37bd448ce01.json');

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertArrayHasKey('artist', $jsonContent);
        $this->assertEquals('7746d775-9550-4360-b8d5-c37bd448ce01', $jsonContent['artist']['gid']);

        // Related links
        $this->assertCount(2, $jsonContent['artist']['latest_segment_events']);
        $this->assertEquals('sv000003', $jsonContent['artist']['latest_segment_events'][0]['pid']);
        $this->assertEquals('sv000002', $jsonContent['artist']['latest_segment_events'][1]['pid']);
    }
}
