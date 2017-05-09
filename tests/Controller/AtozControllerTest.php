<?php

namespace Tests\BBC\CliftonBundle\Controller;

use BBC\ProgrammesPagesService\Service\AtozTitlesService;
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
        return [
            ['/aps/programmes/a-z.json', AtozTitlesService::LETTERS],
            ['/aps/programmes/a-z/player.json', AtozTitlesService::LETTERS],
            ['/aps/programmes/a-z/all.json', AtozTitlesService::LETTERS],
        ];
    }

    /**
     * @dataProvider letterUrlProvider
     */
    public function testByLetter($url, $expectedPids)
    {
        $this->loadFixtures(['AtozTitleFixture']);

        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $actualPids = array_map(function ($atozTitle) {
            return $atozTitle['programme']['pid'];
        }, $jsonContent['atoz']['tleo_titles']);

        $this->assertEquals($expectedPids, $actualPids);
    }


    public function letterUrlProvider()
    {
        return [
            ['/aps/programmes/a-z/by/@.json', ['b0000002']],
            ['/aps/programmes/a-z/by/@/player.json', ['b0000002']],
            ['/aps/programmes/a-z/by/m/all.json', ['b0020020', 'b010t19z']],
        ];
    }

    /**
     * @dataProvider letterUrl404Provider
     */
    public function testByLetter404($url)
    {
        $this->loadFixtures(['AtozTitleFixture']);

        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 404);
    }

    public function letterUrl404Provider()
    {
        return [
            ['/aps/programmes/a-z/by/t.json'],
            ['/aps/programmes/a-z/by/t/player.json'],
            ['/aps/programmes/a-z/by/c/all.json'],
        ];
    }
}
