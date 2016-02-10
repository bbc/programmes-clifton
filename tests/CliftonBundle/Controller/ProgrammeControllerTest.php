<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

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
}
