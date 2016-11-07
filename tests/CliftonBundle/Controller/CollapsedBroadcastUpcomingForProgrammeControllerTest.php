<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use DateTimeImmutable;

/**
 * This class includes mocking of services, instead of relying entirely on
 * database fixtures for a full end-to-end test. This is because some of the
 * service calls make calls to MySQL-specific queries which do not work in
 * SQLite.
 */
class CollapsedBroadcastUpcomingForProgrammeControllerTest extends BaseWebTestCase
{
    public function testCollapsedBroadcastLatestForProgramme()
    {
        $bs = $this->mockCollapsedBroadcastsService();

        $bs->expects($this->once())->method('countUpcomingByProgramme')
            ->with($this->callback($this->isProgrammeWithPidFn('b010t19z')))
            ->willReturn(1);

        $bs->expects($this->once())->method('findUpcomingByProgramme')
            ->with(
                $this->callback($this->isProgrammeWithPidFn('b010t19z')),
                30,
                1
            )
            ->willReturn([
                $this->createMockBroadcast('p0000001'),
                $this->createMockBroadcast('p0000002'),
            ]);

        $this->loadFixtures(['MongrelsFixture']);

        $client = static::createClient();
        // Inject the mock Service
        static::$kernel->getContainer()->set('pps.collapsed_broadcasts_service', $bs);
        $client->request('GET', '/aps/programmes/b010t19z/episodes/upcoming.json');

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertArrayHasKey('broadcasts', $jsonContent);
        $this->assertEquals(count($jsonContent['broadcasts']), 2);

        $this->assertEquals($jsonContent['broadcasts'][0]['programme']['pid'], 'p0000001');
        $this->assertEquals($jsonContent['broadcasts'][1]['programme']['pid'], 'p0000002');
    }

    /**
     * @dataProvider upcomingPaginationProvider
     */
    public function testCollapsedBroadcastLatestForProgrammePagination($limit, $page, $expectedLimit, $expectedPage, $expectedOffset)
    {
        $bs = $this->mockCollapsedBroadcastsService();

        $bs->expects($this->once())->method('countUpcomingByProgramme')
            ->with($this->callback($this->isProgrammeWithPidFn('b010t19z')))
            ->willReturn(20);

        $bs->expects($this->once())->method('findUpcomingByProgramme')
            ->with(
                $this->callback($this->isProgrammeWithPidFn('b010t19z')),
                $expectedLimit,
                $expectedPage
            )
            ->willReturn(array_fill(0, 20, $this->createMockBroadcast('p0000001')));

        $this->loadFixtures(['MongrelsFixture']);

        $client = static::createClient();
        // Inject the mock Service
        static::$kernel->getContainer()->set('pps.collapsed_broadcasts_service', $bs);
        $client->request('GET', sprintf('/aps/programmes/b010t19z/episodes/upcoming.json?page=%s&limit=%s', $page, $limit));

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertEquals($expectedPage, $jsonContent['page']);
        $this->assertEquals($expectedOffset, $jsonContent['offset']);
    }

    public function upcomingPaginationProvider()
    {
        return [
            [ '', '', 30, 1, 0], // Use default
            [ '-1', '-1', 30, 1, 0], // Use default - invalid negative numbers
            [ 'a', 'a', 30, 1, 0], // Use default - invalid alphabet inputs

            ['5', '3', 5, 3, 10], // Custom page and limit
        ];
    }

    public function testCollapsedBroadcastLatestForProgrammeWithNoBroadcasts()
    {
        $this->loadFixtures(['MongrelsFixture']);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/b010t19z/episodes/upcoming.json');

        $this->assertResponseStatusCode($client, 404);
    }

    public function testCollapsedBroadcastLatestForProgrammeWithNonExistantProgramme()
    {
        $this->loadFixtures([]);

        $client = static::createClient();
        $client->request('GET', '/aps/programmes/qqqqqqqq/episodes/upcoming.json');

        $this->assertResponseStatusCode($client, 404);
    }

    private function mockCollapsedBroadcastsService()
    {
        return $this->createMock('BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService');
    }

    private function isProgrammeWithPidFn($pid)
    {
        return (function ($programme) use ($pid) {
            return $programme->getPid() == $pid;
        });
    }

    private function createMockBroadcast($programmePid)
    {
        $mockService = $this->createMock('BBC\ProgrammesPagesService\Domain\Entity\Service');
        $mockService->method('getNetwork')->willReturn(
            $this->createMock('BBC\ProgrammesPagesService\Domain\Entity\Network')
        );

        $mockProgramme = $this->createMock('BBC\ProgrammesPagesService\Domain\Entity\Episode');
        $mockProgramme->method('getPid')->willReturn(new Pid($programmePid));
        $mockProgramme->method('getFirstBroadcastDate')->willReturn(new DateTimeImmutable());

        $mockBroadcast = $this->createMock('BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast');
        $mockBroadcast->method('getProgrammeItem')->willReturn($mockProgramme);
        $mockBroadcast->method('getServices')->willReturn([$mockService]);
        $mockBroadcast->method('getStartAt')->willReturn(new DateTimeImmutable());
        $mockBroadcast->method('getEndAt')->willReturn(new DateTimeImmutable());

        return $mockBroadcast;
    }
}
