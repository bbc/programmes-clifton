<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

/**
 * @covers BBC\CliftonBundle\Controller\FindByPidController
 */
class FindByPidControllerTest extends BaseWebTestCase
{
    public function testFindByPidActionWithBrand()
    {
        $this->loadFixtures(['EastendersFixture']);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/b006m86d.json');

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertArrayHasKey('programme', $jsonContent);
        $this->assertEquals('b006m86d', $jsonContent['programme']['pid']);
        $this->assertEquals('brand', $jsonContent['programme']['type']);

        // Parent tree
        $this->assertArrayNotHasKey('parent', $jsonContent['programme']);

        // Siblings
        $this->assertArrayNotHasKey('peers', $jsonContent['programme']);

        // Related links
        $this->assertCount(2, $jsonContent['programme']['links']);
        $this->assertEquals('RL1', $jsonContent['programme']['links'][0]['title']);
    }

    public function testFindByPidActionWithSeries()
    {
        $this->loadFixtures(['EastendersFixture']);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/b006m86f.json');

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertArrayHasKey('programme', $jsonContent);
        $this->assertEquals('b006m86f', $jsonContent['programme']['pid']);
        $this->assertEquals('series', $jsonContent['programme']['type']);

        // Parent tree
        $this->assertEquals('b006m86d', $jsonContent['programme']['parent']['programme']['pid']);
        $this->assertArrayNotHasKey('parent', $jsonContent['programme']['parent']);

        // Siblings
        $this->assertNull($jsonContent['programme']['peers']['next']);
        $this->assertNull($jsonContent['programme']['peers']['previous']);

        // Related links
        $this->assertSame([], $jsonContent['programme']['links']);

        // Siblings
        $this->assertNull($jsonContent['programme']['peers']['next']);
        $this->assertNull($jsonContent['programme']['peers']['previous']);
    }

    public function testFindByPidActionWithEpisode()
    {
        $this->loadFixtures(['EastendersFixture']);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/b06khpp1.json');

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertArrayHasKey('programme', $jsonContent);
        $this->assertEquals('b06khpp1', $jsonContent['programme']['pid']);
        $this->assertEquals('episode', $jsonContent['programme']['type']);

        // Parent tree
        $this->assertEquals('b006m86f', $jsonContent['programme']['parent']['programme']['pid']);
        $this->assertEquals('b006m86d', $jsonContent['programme']['parent']['programme']['parent']['programme']['pid']);
        $this->assertArrayNotHasKey('parent', $jsonContent['programme']['parent']['programme']['parent']);

        // Related links
        $this->assertSame([], $jsonContent['programme']['links']);

        //Siblings
        $this->assertNotNull($jsonContent['programme']['peers']['previous']);
        $this->assertEquals('b06khpp0', $jsonContent['programme']['peers']['previous']['pid']);
        $this->assertNotNull($jsonContent['programme']['peers']['next']);
        $this->assertEquals('b06khpp2', $jsonContent['programme']['peers']['next']['pid']);

        // Versions
        $this->assertSame([], $jsonContent['programme']['versions']);
    }

    public function testFindByPidActionWithClip()
    {
        $this->loadFixtures(['EastendersFixture']);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/b06khpq0.json');

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertArrayHasKey('programme', $jsonContent);
        $this->assertEquals('b06khpq0', $jsonContent['programme']['pid']);
        $this->assertEquals('clip', $jsonContent['programme']['type']);

        // Parent tree
        $this->assertEquals('b06khpp0', $jsonContent['programme']['parent']['programme']['pid']);
        $this->assertEquals('b006m86f', $jsonContent['programme']['parent']['programme']['parent']['programme']['pid']);
        $this->assertEquals('b006m86d', $jsonContent['programme']['parent']['programme']['parent']['programme']['parent']['programme']['pid']);
        $this->assertArrayNotHasKey('parent', $jsonContent['programme']['parent']['programme']['parent']['programme']['parent']);

        // Related links
        $this->assertSame([], $jsonContent['programme']['links']);

        // Siblings
        $this->assertNull($jsonContent['programme']['peers']['next']);
        $this->assertNull($jsonContent['programme']['peers']['previous']);

        // Versions
        $this->assertCount(2, $jsonContent['programme']['versions']);
        $this->assertEquals('b06khpr0', $jsonContent['programme']['versions'][0]['pid']);
    }

    public function testFindByPidActionWithEmptyResult()
    {
        $this->loadFixtures([]);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/qqqqqqqq.json');

        $this->assertResponseStatusCode($client, 404);
    }
}
