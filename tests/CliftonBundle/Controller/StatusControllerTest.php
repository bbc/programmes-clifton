<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

class StatusControllerTest extends BaseWebTestCase
{
    public function testStatus()
    {
        $this->loadFixtures(['SimpleStatusFixture']);

        $client = static::createClient();
        $crawler = $client->request('GET', '/status');
        $this->assertResponseStatusCode($client, 200);

        $this->assertEquals('YES', $crawler->filter('[data-test-name=db-connectivity] span')->text());
    }

    public function testStatusFromElb()
    {
        $client = static::createClient([], [
            'HTTP_USER_AGENT' => 'ELB-HealthChecker/1.0',
        ]);
        $crawler = $client->request('GET', '/status');

        $this->assertResponseStatusCode($client, 200);
        $this->assertEquals('OK', $client->getResponse()->getContent());
    }
}
