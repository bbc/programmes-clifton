<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;

/**
 * @covers BBC\CliftonBundle\Controller\AtozController
 */
class AtozControllerTest extends BaseWebTestCase
{
    /**
     * @dataProvider lettersListUrlProvider
     */
    public function testLettersListAction($url, $letters)
    {
        $this->loadFixtures(['AtozTitleFixture']);

        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);
        unset($jsonContent['atoz']['service_group']);
        $expectedOutput = [
            'atoz' => [
                'slice' => 'player',
                'by' => null,
                'letters' => $letters,
                'page' => null,
                'total' => null,
                'offset' => null,
                'tleo_titles' => [],
            ],
        ];
        $this->assertEquals($expectedOutput, $jsonContent);
    }

    public function lettersListUrlProvider()
    {
        // Do we have enough routes for this crap?
        return [
            ['/aps/programmes/a-z.json', ['@', 'm', 't', 'w']],
            ['/aps/programmes/a-z/player.json', ['@', 'm', 't', 'w']],
            ['/aps/programmes/a-z/all.json', ['@', 'm', 't', 'w']],
            ['/aps/radio/programmes/a-z.json', ['m']],
            ['/aps/radio/programmes/a-z/player.json', ['m']],
            ['/aps/radio/programmes/a-z/all.json', ['m']],
            ['/aps/tv/programmes/a-z.json', ['m']],
            ['/aps/tv/programmes/a-z/player.json', ['m']],
            ['/aps/tv/programmes/a-z/all.json', ['m']],
        ];
    }

    /**
     * @dataProvider testByLetterUrlProvider
     */
    public function testByLetter($url, $output)
    {
        $this->loadFixtures(['AtozTitleFixture']);

        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $atozTitles = $jsonContent['atoz']['tleo_titles'];

        $this->assertCount(count($output['programmePids']), $atozTitles);
        $i = 0;
        foreach ($atozTitles as $title) {
            $realPid = $title['programme']['pid'];
            $expectedPid = $output['programmePids'][$i] ?: null;
            $this->assertEquals($expectedPid, $realPid);
            $i++;
        }
    }

    public function testByLetterUrlProvider()
    {
        return [
            [
                '/aps/programmes/a-z/by/@.json',
                [
                    'programmePids' => ['b0000002'],
                ],
            ],
            [
                '/aps/programmes/a-z/by/@/player.json',
                [
                    'programmePids' => ['b0000002'],
                ],
            ],
            [
                '/aps/programmes/a-z/by/m/all.json',
                [
                    'programmePids' => ['b0020020', 'b010t19z'],
                ],
            ],
            [
                '/aps/radio/programmes/a-z/by/m.json',
                [
                    'programmePids' => ['b0020020'],
                ],
            ],
            [
                '/aps/radio/programmes/a-z/by/m/player.json',
                [
                    'programmePids' => ['b0020020'],
                ],
            ],
            [
                '/aps/radio/programmes/a-z/by/m/all.json',
                [
                    'programmePids' => ['b0020020'],
                ],
            ],
            [
                '/aps/tv/programmes/a-z/by/m.json',
                [
                    'programmePids' => [],
                ],
            ],
            [
                '/aps/tv/programmes/a-z/by/m/player.json',
                [
                    'programmePids' => [],
                ],
            ],
            [
                '/aps/tv/programmes/a-z/by/m/all.json',
                [
                    'programmePids' => ['b010t19z'],
                ],
            ],
        ];
    }
}
