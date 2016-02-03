<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

class StatusControllerTest extends BaseWebTestCase
{
    public function testStatusAnonymous()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/status');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('OK', $client->getResponse()->getContent());
    }

    public function testStatusAuthenticated()
    {
        $this->loadFixtures(['SimpleStatusFixture']);

        $client = static::createAuthenticatedClient();
        $crawler = $client->request('GET', '/status');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('Status', $crawler->filter('h1')->text());
        $this->assertEquals('Pips Lag: 0 Years, 0 Months, 0 Days, 0 Hours, 1 Minutes, 0 Seconds', $crawler->filter('p')->text());
    }

    public function testStatusFromElb()
    {
        $client = static::createClient(array(), array(
            'HTTP_USER_AGENT' => 'ELB-HealthChecker/1.0',
        ));
        $crawler = $client->request('GET', '/status');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('OK', $client->getResponse()->getContent());
    }
}
