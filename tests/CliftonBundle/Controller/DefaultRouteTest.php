<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

class DefaultRouteTest extends BaseWebTestCase
{
    public function testItRedirectsToTheApiDocRoute()
    {
        $client = static::createAuthenticatedClient();
        $crawler = $client->request('GET', '/');

        $this->assertRedirectTo($client, 301, 'http://localhost/apidoc');
    }
}
