<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

/**
 * @covers BBC\CliftonBundle\Controller\FindByPidController
 */
class FindByPidControllerTest extends BaseWebTestCase
{
    public function testFindByPidAction()
    {
        $this->loadFixtures(['EastendersFixture']);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/b006m86d.json');

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertArrayHasKey('programme', $jsonContent);
        $this->assertEquals('b006m86d', $jsonContent['programme']['pid']);
    }

    public function testFindByPidActionWithEmptyResult()
    {
        $this->loadFixtures([]);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/qqqqqqqq.json');

        $this->assertResponseStatusCode($client, 404);
    }
}
