<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

/**
 * @covers BBC\CliftonBundle\Controller\MusicArtistsController
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

    public function testMusicChart()
    {
        $this->loadFixtures(['SegmentEventsForArtistsFixture']);

        $client = static::createClient();
        $client->getContainer()->set(
            'clifton.application_time',
            new \DateTimeImmutable('2016-07-06T12:00:00Z')
        );
        $client->request('GET', '/aps/programmes/music/artists/charts.json');

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertArrayHasKey('artists_chart', $jsonContent);
        $this->assertSame('2016-06-29', $jsonContent['artists_chart']['start']);
        $this->assertSame('2016-07-06', $jsonContent['artists_chart']['end']);
        $this->assertSame('Past 7 days', $jsonContent['artists_chart']['period']);

        $artists = $jsonContent['artists_chart']['artists'];
        $this->assertSame(2, count($artists));

        $this->assertSame('7746d775-9550-4360-b8d5-c37bd448ce01', $artists[0]['gid']);
        $this->assertSame(2, $artists[0]['plays']);
        $this->assertSame(0, $artists[0]['previous_plays']);

        $this->assertSame('028e1863-cab4-4a3d-9dd9-91c682c91005', $artists[1]['gid']);
        $this->assertSame(1, $artists[1]['plays']);
        $this->assertSame(0, $artists[1]['previous_plays']);
    }

    public function testMusicChartWithNonExistingService()
    {
        $this->loadFixtures(['SegmentEventsForArtistsFixture']);

        $client = static::createClient();
        $client->request('GET', '/aps/notreal/programmes/music/artists/charts.json');

        $this->assertResponseStatusCode($client, 404);
    }

    public function testMusicChartWithService()
    {
        $this->loadFixtures(['SegmentEventsForArtistsFixture']);

        $client = static::createClient();
        $client->getContainer()->set(
            'clifton.application_time',
            new \DateTimeImmutable('2016-07-06T12:00:00Z')
        );
        $client->request('GET', '/aps/radio2/programmes/music/artists/charts.json');

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);
        $this->assertSame('2016-06-29', $jsonContent['artists_chart']['start']);
        $this->assertSame('2016-07-06', $jsonContent['artists_chart']['end']);
        $this->assertSame('radio2', $jsonContent['artists_chart']['service']['key']);
        $this->assertSame('Past 7 days', $jsonContent['artists_chart']['period']);

        $artists = $jsonContent['artists_chart']['artists'];
        $this->assertSame(1, count($artists));

        $this->assertSame('7746d775-9550-4360-b8d5-c37bd448ce01', $artists[0]['gid']);
        $this->assertSame(1, $artists[0]['plays']);
        $this->assertSame(0, $artists[0]['previous_plays']);
    }
}
