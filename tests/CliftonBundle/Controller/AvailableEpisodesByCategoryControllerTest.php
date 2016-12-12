<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\BBC\CliftonBundle\BaseWebTestCase;

/**
 * @covers BBC\CliftonBundle\Controller\AvailableEpisodesByCategoryController
 */
class AvailableEpisodesByCategoryControllerTest extends BaseWebTestCase
{
    /**
     * @dataProvider validRoutesProvider
     */
    public function testCategoryMetadataValidRoutes($url)
    {
        $this->loadFixtures(['MongrelsWithCategoriesFixture']);

        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 200);
    }

    public function validRoutesProvider()
    {
        return [
            ['/aps/programmes/genres/comedy/player/episodes.json'],
            ['/aps/programmes/genres/comedy/sitcoms/player/episodes.json'],
            ['/aps/programmes/genres/comedy/sitcoms/puppetysitcoms/player/episodes.json'],
            ['/aps/tv/programmes/genres/comedy/sitcoms/puppetysitcoms/player/episodes.json'],
            ['/aps/programmes/formats/animation/player/episodes.json'],
        ];
    }

    /**
     * @dataProvider invalidRoutesProvider
     */
    public function testCategoryMetadataInvalidRoutes($url)
    {
        $this->loadFixtures(['MongrelsWithCategoriesFixture']);

        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 404);
    }

    public function invalidRoutesProvider()
    {
        return [
            ['/aps/programmes/genres/comedy/sitcoms/puppetysitcoms/extralevel/player/episodes.json'],
            ['/aps/programmes/genres/notinthere/player/episodes.json'],
            ['/aps/programmes/format/with/2levels/player/episodes.json'],
            ['/aps/microwave/programmes/genres/comedy/player/episodes.json'],
        ];
    }

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
