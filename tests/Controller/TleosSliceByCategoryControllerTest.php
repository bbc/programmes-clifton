<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

/**
 * @covers BBC\CliftonBundle\Controller\TleosSliceByCategoryController
 */
class TleosSliceByCategoryControllerTest extends BaseWebTestCase
{
    protected function setUp()
    {
        /**
         * From fixture:
         *
         *  brand1
         *      cat1/cat11          (category) - /1/2
         *      form1               (format) - /4
         *      streamable=True     (availability)
         *
         *  brand2
         *      cat1/cat11/cat111   (category) - /1/2/3
         *      form2               (format) - /5
         *      streamable=False    (availability)
         */
        $this->loadFixtures(['TleosByCategoryFixture']);
    }

    /**
     * tests to check that is returning all the programmes in the right category (slice=all)
     */
    public function testShowProgrammesInAllSliceReturnAllProgrammesInGenreCategory()
    {
        $client = static::createClient();
        $client->request('GET', "/aps/programmes/genres/cat1/cat11/all.json");

        $this->assertResponseStatusCode($client, 200);
        $programmesInSlice = $this->getDecodedJsonContent($client);
        $this->assertCount(2, $programmesInSlice['category_slice']['programmes']);
    }

    /**
     * @dataProvider tleosSliceByCategoryPaginationProvider
     */
    public function testTleosSliceByCategoryPagination($limit, $page, $expectedOffset, $expectedPage)
    {
        $client = static::createClient();
        $client->request('GET', sprintf('/aps/programmes/genres/cat1/cat11/all.json?limit=%s&page=%s', $limit, $page));

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertEquals($expectedPage, $jsonContent['page']);
        $this->assertEquals($expectedOffset, $jsonContent['offset']);
    }

    public function tleosSliceByCategoryPaginationProvider()
    {
        return [
            [ '', '', 0, 1], // Use default
            [ '-1', '-1', 0, 1], // Use default - invalid negative numbers
            [ 'a', 'a', 0, 1], // Use default - invalid alphabet inputs
            ['1', '2', 1, 2], // Custom page and limit
        ];
    }

    public function testShowProgrammesInAllSliceReturnAllProgrammesInGenreTreeCategory()
    {
        $client = static::createClient();
        $client->request('GET', "/aps/programmes/genres/cat1/cat11/cat111/all.json");

        $this->assertResponseStatusCode($client, 200);
        $programmesInSlice = $this->getDecodedJsonContent($client);
        $this->assertCount(1, $programmesInSlice['category_slice']['programmes']);
    }

    /**
     * test to check that is returning streamable programmes in the right category (slice=player)
     */
    public function testShowProgrammesInPlayerSliceReturnOnlyStreamableProgrammesInGenreCategory()
    {
        $client = static::createClient();
        $client->request('GET', "/aps/programmes/genres/cat1/cat11/player.json");

        $this->assertResponseStatusCode($client, 200);
        $programmesInSlice = $this->getDecodedJsonContent($client);
        $this->assertCount(1, $programmesInSlice['category_slice']['programmes']);
    }

    public function testShowProgrammesInPlayerSliceReturnStreamableProgrammesInGenreCategory()
    {
        $client = static::createClient();
        $client->request('GET', "/aps/programmes/genres/cat1/cat11/player.json");

        $this->assertResponseStatusCode($client, 200);
        $programmesInSlice = $this->getDecodedJsonContent($client);
        $this->assertCount(1, $programmesInSlice['category_slice']['programmes']);
    }

    public function testShowProgrammesInAllSliceReturnEmptyProgrammesInGenreTreeCategory()
    {
        $client = static::createClient();
        $client->request('GET', "/aps/programmes/genres/cat1/cat11/cat111/player.json");

        $this->assertResponseStatusCode($client, 200);
        $programmesInSlice = $this->getDecodedJsonContent($client);
        $this->assertCount(0, $programmesInSlice['category_slice']['programmes']);
    }
}
