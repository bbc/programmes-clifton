<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

/**
 * @covers BBC\CliftonBundle\Controller\CategoriesListController
 */
class CategoriesListControllerTest extends BaseWebTestCase
{
    public function testGenres()
    {
        $this->loadFixtures(['MongrelsWithCategoriesFixture']);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/genres.json');
        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertArrayHasKey('categories', $jsonContent);
        $this->assertEquals(count($jsonContent['categories']), 1);
        $this->assertEquals($jsonContent['categories'][0]['id'], 'C00193');

        $this->assertArrayHasKey('narrower', $jsonContent['categories'][0]);
        $this->assertEquals(count($jsonContent['categories'][0]['narrower']), 1);
        $this->assertEquals($jsonContent['categories'][0]['narrower'][0]['id'], 'C00196');
    }

    public function testFormat()
    {
        $this->loadFixtures(['MongrelsWithCategoriesFixture']);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/formats.json');
        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertArrayHasKey('categories', $jsonContent);
        $this->assertEquals(count($jsonContent['categories']), 1);
        $this->assertEquals($jsonContent['categories'][0]['id'], 'PT001');
    }
}
