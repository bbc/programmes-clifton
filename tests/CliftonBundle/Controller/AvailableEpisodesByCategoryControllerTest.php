<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\BBC\CliftonBundle\BaseWebTestCase;

/**
 * @covers BBC\CliftonBundle\Controller\AvailableEpisodesByCategoryController
 */
class AvailableEpisodesByCategoryControllerTest extends BaseWebTestCase
{
    public function testGenreAvailableEpisodesByCategoryAction()
    {
        $this->loadFixtures(['MongrelsWithCategoriesFixture']);

        $client = static::createClient();

        $client->request('GET', '/aps/programmes/genres/comedy/sitcoms/puppetysitcoms/player/episodes.json');
        $this->assertResponseStatusCode($client, 200);
        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertArrayHasKey('episodes', $jsonContent);
        $this->assertEquals(count($jsonContent['episodes']), 1);

        $this->assertEquals($jsonContent['episodes'][0]['programme']['type'], 'episode');
        $this->assertEquals($jsonContent['episodes'][0]['programme']['pid'], 'b0175lqm');

        $this->assertArrayHasKey('display_titles', $jsonContent['episodes'][0]['programme']);
        $this->assertEquals($jsonContent['episodes'][0]['programme']['display_titles']['title'], 'Mongrels');
        $this->assertEquals($jsonContent['episodes'][0]['programme']['display_titles']['subtitle'], 'Series 2, Episode 1');
    }

    public function testGenreAvailableEpisodesByCategoryActionMediumRoute()
    {
        $this->loadFixtures(['MongrelsWithCategoriesFixture']);

        $client = static::createClient();

        $client->request('GET', '/aps/tv/programmes/genres/comedy/sitcoms/puppetysitcoms/player/episodes.json');
        $this->assertResponseStatusCode($client, 200);
        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertArrayHasKey('episodes', $jsonContent);
        $this->assertEquals(count($jsonContent['episodes']), 1);

        $this->assertEquals($jsonContent['episodes'][0]['programme']['type'], 'episode');
        $this->assertEquals($jsonContent['episodes'][0]['programme']['pid'], 'b0175lqm');

        $this->assertArrayHasKey('display_titles', $jsonContent['episodes'][0]['programme']);
        $this->assertEquals($jsonContent['episodes'][0]['programme']['display_titles']['title'], 'Mongrels');
        $this->assertEquals($jsonContent['episodes'][0]['programme']['display_titles']['subtitle'], 'Series 2, Episode 1');
    }

    public function testGenreAvailableEpisodesByCategoryActionInvalidCategory()
    {
        $this->loadFixtures(['MongrelsWithCategoriesFixture']);

        $client = static::createClient();

        // Categories don't exist in the fixtures
        $client->request('GET', '/aps/programmes/genres/bad/badder/baddest/player/episodes.json');
        $this->assertResponseStatusCode($client, 404);
    }

    public function testGenreAvailableEpisodesByCategoryActionInvalidPageNumber()
    {
        $this->loadFixtures(['MongrelsWithCategoriesFixture']);

        $client = static::createClient();

        // Incorrect page number
        $client->request('GET', '/aps/programmes/genres/comedy/sitcoms/puppetysitcoms/player/episodes.json?page=9');
        $this->assertResponseStatusCode($client, 404);
    }

    public function testGenreAvailableEpisodesByCategoryActionNoEpisodesFound()
    {
        $this->loadFixtures(['MongrelsWithCategoriesFixture']);

        $client = static::createClient();

        // No episodes for that category
        $client->request('GET', '/aps/programmes/genres/comedy/sitcoms/britishsitcoms/player/episodes.json');
        $this->assertResponseStatusCode($client, 404);
    }
}
