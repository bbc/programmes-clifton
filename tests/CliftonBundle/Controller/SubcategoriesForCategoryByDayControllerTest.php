<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

class SubcategoriesForCategoryByDayControllerTest extends BaseWebTestCase
{
    protected function setUp()
    {
        $this->loadFixtures(['CollapsedBroadcastsWithCategoriesFixture']);
    }

    /**
     * test to check that the structure response is the corrrect for differente genre cases
     */
    public function testIsReturningNarrowerResultsOnly()
    {
        $client = static::createClient();
        $client->request('GET', "/aps/programmes/genres/cat1/schedules/2016/10/04/subcategories.json");

        $this->assertResponseStatusCode($client, 200);
        $broadcastedCategories = $this->getDecodedJsonContent($client);

        $this->assertEquals('cat1', $broadcastedCategories['category']['key']);
        $this->assertEquals('cat11', $broadcastedCategories['category']['narrower'][0]['key']);
        $this->assertCount(0, $broadcastedCategories['category']['broader']);
    }

    public function testIsReturningBroaderResultsOnly()
    {
        $client = static::createClient();
        $client->request('GET', "/aps/programmes/genres/cat1/cat11/cat111/schedules/2016/10/04/subcategories.json");

        $this->assertResponseStatusCode($client, 200);
        $broadcastedCategories = $this->getDecodedJsonContent($client);

        $this->assertEquals('cat111', $broadcastedCategories['category']['key']);
        $this->assertCount(0, $broadcastedCategories['category']['narrower']);
        // test exist nested category parents
        $this->assertEquals('cat11', $broadcastedCategories['category']['broader']['category']['key']);
        $this->assertEquals('cat1', $broadcastedCategories['category']['broader']['category']['broader']['category']['key']);
    }

    public function testIsReturningBroaderAndNarrowerResults()
    {
        $client = static::createClient();
        $client->request('GET', "/aps/programmes/genres/cat1/cat11/schedules/2016/10/04/subcategories.json");

        $this->assertResponseStatusCode($client, 200);
        $broadcastedCategories = $this->getDecodedJsonContent($client);
        $this->assertEquals('cat11', $broadcastedCategories['category']['key']);
        // test exist child(narrower) and parents (broader) of category selected
        $this->assertEquals('cat111', $broadcastedCategories['category']['narrower'][0]['key']);
        $this->assertEquals('cat1', $broadcastedCategories['category']['broader']['category']['key']);
    }

    /**
     * test to check that the structure response is the corrrect for formats
     */
    public function testIsReturningOnlyOneFormat()
    {
        $client = static::createClient();
        $client->request('GET', "/aps/programmes/formats/form1/schedules/2016/10/04/subcategories.json");

        $this->assertResponseStatusCode($client, 200);
        $broadcastedCategories = $this->getDecodedJsonContent($client);
        $this->assertCount(0, $broadcastedCategories['category']['narrower']);
        $this->assertCount(0, $broadcastedCategories['category']['broader']);
    }
}
