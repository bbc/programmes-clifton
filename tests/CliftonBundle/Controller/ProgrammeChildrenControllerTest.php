<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

/**
 * @covers BBC\CliftonBundle\Controller\ProgrammeChildrenController
 * @covers BBC\CliftonBundle\Controller\BaseApsController
 */
class ProgrammeChildrenControllerTest extends BaseWebTestCase
{
    public function testChildrenAction()
    {
        $this->loadFixtures(['EastendersFixture']);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/b006m86d/children.json');

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertCount(1, $jsonContent->children->programmes);
        $this->assertEquals(1, $jsonContent->children->page);
        $this->assertEquals(0, $jsonContent->children->offset);
    }

    /**
     * @dataProvider childrenPaginationProvider
     */
    public function testChildrenPagination($limit, $page, $expectedPage, $expectedOffset)
    {
        $this->loadFixtures(['EastendersFixture']);

        $client = static::createClient();
        $client->request('GET', sprintf('/aps/programmes/b006m86d/children.json?page=%s&limit=%s', $page, $limit));

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertEquals($expectedPage, $jsonContent->children->page);
        $this->assertEquals($expectedOffset, $jsonContent->children->offset);
    }

    public function childrenPaginationProvider()
    {
        return [
            [ '', '', 1, 0], // Use default
            [ '-1', '-1', 1, 0], // Use default - invalid negative numbers
            [ 'a', 'a', 1, 0], // Use default - invalid alphabet inputs

            ['5', '3', 3, 10], // Custom page and limit
        ];
    }
}
