<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

class CategoryCalendarControllerTest extends BaseWebTestCase
{
    /**
     * @dataProvider validRoutesProvider
     */
    public function testCategoryCalendarValidRoutes($url, $expectedResult)
    {
        $this->loadFixtures(['BroadcastsFixture']);

        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);
        $this->assertEquals($jsonContent, $expectedResult);
    }

    public function validRoutesProvider()
    {
        return [
            [
                '/aps/programmes/genres/comedy/schedules/2015/09/calendar.json',
                [
                    'month' => [
                        'date' => '2015-09-01',
                        'has_previous_month' => true,
                        'has_next_month' => true,
                        'active_days' => [
                            'previous_month' => [1, 2],
                            'this_month' => [3],
                            'next_month' => [1],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidRoutesProvider
     */
    public function testCategoryMetadataInvalidRoutes($url)
    {
        $this->loadFixtures(['BroadcastsFixture']);

        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 404);
    }

    public function invalidRoutesProvider()
    {
        return [
            ['/aps/programmes/genres/comedy/sitcoms/puppetysitcoms/extralevel/schedules/2015/08/calendar.json'],
            ['/aps/programmes/genres/notinthere/2015/08/calendar.json'],
            ['/aps/programmes/format/with/2levels/2015/08/calendar.json'],
        ];
    }
}
