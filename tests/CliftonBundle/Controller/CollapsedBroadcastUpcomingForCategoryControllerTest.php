<?php

namespace CliftonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Tests\BBC\CliftonBundle\BaseWebTestCase;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use DateTimeImmutable;

/**
 * @covers BBC\CliftonBundle\Controller\CollapsedBroadcastUpcomingForCategoryController
 */
class CollapsedBroadcastUpcomingForCategoryControllerTest extends BaseWebTestCase
{
    /** @var  Client */
    private $client;

    /**
     * Tetst amount of programmes returned
     */
    public function testCollapsedBroadcastUpcomingForCategoryAmountReturnTwoProgrammes()
    {
        $this->client->request('GET', "/aps/programmes/genres/comedy/schedules/upcoming.json");
        $this->assertResponseStatusCode($this->client, 200);
        $programmesInSlice = $this->getDecodedJsonContent($this->client);
        $this->assertCount(10, $programmesInSlice['broadcasts']);
    }

    /**
     * Tetst page/offset functionality
     */
    public function testCollapsedBroadcastUpcomingForCategoryAmountReturnCorrectDefaultValues()
    {
        $this->client->request('GET', "/aps/programmes/genres/comedy/schedules/upcoming.json");
        $this->assertResponseStatusCode($this->client, 200);
        $programmesInSlice = $this->getDecodedJsonContent($this->client);

        $this->assertEquals(1, $programmesInSlice['page']);
        $this->assertEquals(0, $programmesInSlice['offset']);
    }

    public function testCollapsedBroadcastUpcomingForCategoryAmountReturnCorrectCustomValues()
    {
        $this->client->request('GET', "/aps/programmes/genres/comedy/schedules/upcoming.json?page=3&limit=4");
        $this->assertResponseStatusCode($this->client, 200);
        $programmesInSlice = $this->getDecodedJsonContent($this->client);

        $this->assertEquals(3, $programmesInSlice['page']);
        $this->assertEquals(8, $programmesInSlice['offset']);
    }

    protected function setUp()
    {
        $this->loadFixtures(['MongrelsWithCategoriesFixture']);
        // create a few mocks to make possible to test offset/page param features.
        $listMockBroadcasts = [
            $this->createMockBroadcast('p0000001'),
            $this->createMockBroadcast('p0000002'),
            $this->createMockBroadcast('p0000003'),
            $this->createMockBroadcast('p0000004'),
            $this->createMockBroadcast('p0000005'),
            $this->createMockBroadcast('p0000006'),
            $this->createMockBroadcast('p0000007'),
            $this->createMockBroadcast('p0000008'),
            $this->createMockBroadcast('p0000009'),
            $this->createMockBroadcast('p0000010'),
        ];

        // mock container service
        $broadcastsService = $this->createMock('BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService');
        $broadcastsService->expects($this->once())
                          ->method('countByCategoryAndEndAtDateRange')
                          ->willReturn(count($listMockBroadcasts));

        $broadcastsService->expects($this->once())
                          ->method('findByCategoryAndEndAtDateRange')
                          ->willReturn($listMockBroadcasts);

        $client = static::createClient();
        $client->getContainer()->set('pps.collapsed_broadcasts_service', $broadcastsService);

        $this->client = $client;
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
