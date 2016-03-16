<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

/**
 * @covers BBC\CliftonBundle\Controller\ProgrammeController
 */
class ProgrammeControllerTest extends BaseWebTestCase
{
    public function testFindByPidAction()
    {
        $this->loadFixtures(['EastendersFixture']);

        $client = static::createClient();
        $client->request('GET', '/programmes/b006m86d.json');

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertObjectHasAttribute('programme', $jsonContent);
        $this->assertEquals('b006m86d', $jsonContent->programme->pid);
    }

    public function testFindByPidActionWithEmptyResult()
    {
        $this->loadFixtures([]);

        $client = static::createClient();
        $client->request('GET', '/programmes/qqqqqqqq.json');

        $this->assertResponseStatusCode($client, 404);
    }
}
