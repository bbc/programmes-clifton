<?php

namespace Tests\CliftonBundle\Controller;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Tests\BBC\CliftonBundle\BaseWebTestCase;
use DateTimeImmutable;

/**
 * @covers BBC\CliftonBundle\Controller\CollapsedBroadcastLatestForProgrammeController
 */
class CollapsedBroadcastsForMonthTest extends BaseWebTestCase
{
    private $client;

    public function setUp()
    {
        $this->loadFixtures(['MongrelsFixture']);
        $this->client = static::createClient();
    }

    public function testCollapsedBroadcastsForMonth()
    {
        $this->client->getContainer()->set('pps.collapsed_broadcasts_service', $this->mockCollapsedBroadcastsService());

        $this->client->request('GET', "/aps/programmes/b00swyx1/episodes/2016/10.json");

        $this->assertResponseStatusCode($this->client, 200);
        $broadcasts = $this->getDecodedJsonContent($this->client);
        $this->assertEquals(2, count($broadcasts['broadcasts']));
        $this->assertEquals('p0000001', $broadcasts['broadcasts'][0]['programme']['pid']);
        $this->assertEquals('2016-10-02', $broadcasts['broadcasts'][0]['schedule_date']);
        $this->assertEquals('p0000002', $broadcasts['broadcasts'][1]['programme']['pid']);
        $this->assertEquals('2016-10-24', $broadcasts['broadcasts'][1]['schedule_date']);
    }

    private function mockCollapsedBroadcastsService()
    {
        $bs = $this->createMock('BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService');

        $bs->expects($this->once())
            ->method('findByProgrammeAndMonth')
            ->with(
                $this->callback($this->isProgrammeWithPidFn('b00swyx1')),
                2016,
                10,
                null
            )->willReturn(
                [
                    $this->createMockCollapsedBroadcastsAt('p0000001', '2016-10-02'),
                    $this->createMockCollapsedBroadcastsAt('p0000002', '2016-10-24'),
                ]
            );

        return $bs;
    }

    private function createMockCollapsedBroadcastsAt($pid, $broadcastDate)
    {
        $mockService = $this->createMock('BBC\ProgrammesPagesService\Domain\Entity\Service');
        $mockService->method('getNetwork')->willReturn(
            $this->createMock('BBC\ProgrammesPagesService\Domain\Entity\Network')
        );

        $mockProgramme = $this->createMock('BBC\ProgrammesPagesService\Domain\Entity\Episode');
        $mockProgramme->method('getPid')->willReturn(new Pid($pid));
        $mockProgramme->method('getFirstBroadcastDate')->willReturn(new DateTimeImmutable());

        $mockCollapsedBroadcast = $this->createMock('BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast');
        $mockCollapsedBroadcast->method('getProgrammeItem')->willReturn($mockProgramme);
        $mockCollapsedBroadcast->method('getServices')->willReturn([$mockService]);
        $mockCollapsedBroadcast->method('getStartAt')->willReturn(new DateTimeImmutable($broadcastDate));
        $mockCollapsedBroadcast->method('getEndAt')->willReturn(new DateTimeImmutable());


        return $mockCollapsedBroadcast;
    }

    private function isProgrammeWithPidFn($pid)
    {
        return (function ($programme) use ($pid) {
            return $programme->getPid() == $pid;
        });
    }
}
