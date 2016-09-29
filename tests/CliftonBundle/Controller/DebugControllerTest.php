<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

class DebugControllerTest extends BaseWebTestCase
{
    public function testDebugWithoutArguments()
    {
        $this->loadFixtures(['EastendersFixture']);

        $client = static::createClient();
        $crawler = $client->request('GET', '/debug/programmes_service/findAll');
        $this->assertResponseStatusCode($client, 200);

        // 7 Items dumped out
        $this->assertEquals(7, $crawler->filter('.sf-dump > samp > .sf-dump-index')->count());
    }

    public function testDebugWithArguments()
    {
        $this->loadFixtures(['EastendersFixture']);

        $client = static::createClient();
        $crawler = $client->request('GET', '/debug/programmes_service/findAll?a=[2,3]');
        $this->assertResponseStatusCode($client, 200);

        // 2 Items dumped out
        $this->assertEquals(2, $crawler->filter('.sf-dump > samp > .sf-dump-index')->count());
    }

    public function testDebugWithPidCastingArguments()
    {
        $this->loadFixtures(['EastendersFixture']);

        $client = static::createClient();
        $crawler = $client->request('GET', '/debug/programmes_service/findByPidFull?a=["b06khpq0"]');
        $this->assertResponseStatusCode($client, 200);

        // 1 Item dumped out
        $this->assertEquals(1, $crawler->filter('.sf-dump > abbr[title="BBC\ProgrammesPagesService\Domain\Entity\Clip"]')->count());
    }

    public function testInvalidArguments()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/debug/programmes_service/findAll?a=foo');
        $this->assertResponseStatusCode($client, 404);
    }

    public function testInvalidServiceName()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/debug/hams_service/findAll');
        $this->assertResponseStatusCode($client, 404);
    }

    public function testInvalidMethodName()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/debug/programmes_service/hams');
        $this->assertResponseStatusCode($client, 404);
    }

    public function testArgumentsAreWrongTypes()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/debug/programmes_service/findAll?a=["foo"]');
        $this->assertResponseStatusCode($client, 500);
    }
}
