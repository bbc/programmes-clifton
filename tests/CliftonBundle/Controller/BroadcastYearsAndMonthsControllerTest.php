<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

/**
 * @covers BBC\CliftonBundle\Controller\BroadcastYearsAndMonthsController
 */
class BroadcastYearsAndMonthsControllerTest extends BaseWebTestCase
{
    public function testYearsAndMonthActionWithBrand()
    {
        $this->loadFixtures(['CollapsedBroadcastsFixture']);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/b017j7vs/episodes.json');

        $this->assertResponseStatusCode($client, 200);

        $expectedYears = [
            ['id' => 2016, 'months' => [ ['id' => 1] ]],
            ['id' => 2015, 'months' => [ ['id' => 10], ['id' => 9], ['id' => 8] ]],
        ];

        $jsonContent = $this->getDecodedJsonContent($client);
        $this->assertSame(['filters' => ['years' => $expectedYears, 'tags' => []]], $jsonContent);
    }

    public function testFindByPidActionWithEmptyResult()
    {
        // The Brand exists but there are no broadcasts
        $this->loadFixtures(['EmbargoedProgrammeFixture']);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/b017j7vs/episodes.json');

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);
        $this->assertSame(['filters' => ['years' => [], 'tags' => []]], $jsonContent);
    }

    public function testYearsAndMonthActionWithEpisode()
    {
        $this->loadFixtures(['EmbargoedProgrammeFixture']);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/b01777fr/episodes.json');

        // Broadcasts listing is only relevant for Brands and Series
        // Don't serve anything for this episode pid
        $this->assertResponseStatusCode($client, 404);
    }
}
