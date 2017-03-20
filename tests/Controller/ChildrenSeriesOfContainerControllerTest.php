<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

class ChildrenSeriesOfContainerControllerTest extends BaseWebTestCase
{
    public function testChildrenSeriesOfContainer()
    {
        $this->loadFixtures(['MongrelsFixture']);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/b010t19z/series.json');

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);
        $this->assertArrayHasKey('programmes', $jsonContent);
        $this->assertEquals(count($jsonContent['programmes']), 2);

        $this->assertEquals($jsonContent['programmes'][0]['pid'], 'b00swyx1');
        $this->assertEquals($jsonContent['programmes'][1]['pid'], 'b010t150');
    }

    public function testChildrenSeriesOfContainerWithoutChildrenSeries()
    {
        $this->loadFixtures(['MongrelsFixture']);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/b00swyx1/series.json');

        $this->assertResponseStatusCode($client, 404);
    }

    public function testChildrenSeriesOfContainerWithEmptyResult()
    {
        $this->loadFixtures([]);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/qqqqqqqq/series.json');

        $this->assertResponseStatusCode($client, 404);
    }
}
