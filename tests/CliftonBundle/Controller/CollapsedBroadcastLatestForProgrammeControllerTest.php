<?php

namespace Tests\BBC\CliftonBundle\Controller;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use DateTimeImmutable;
use Tests\BBC\CliftonBundle\BaseWebTestCase;

/**
 * @covers BBC\CliftonBundle\Controller\CollapsedBroadcastLatestForProgrammeController
 */
class CollapsedBroadcastLatestForProgrammeControllerTest extends BaseWebTestCase
{
    private $client;

    public function setUp()
    {
        $this->loadFixtures(['MongrelsFixture']);
        $this->client = static::createClient();
    }

    public function testCollapsedBroadcastLatestForProgramme()
    {
        $this->client->getContainer()->set('pps.collapsed_broadcasts_service', $this->mockCollapsedBroadcastsService());

        $this->client->request('GET', "/aps/programmes/b010t150/episodes/last.json");

        $this->assertResponseStatusCode($this->client, 200);
        $broadcastsResponse = $this->getDecodedJsonContent($this->client);
        $this->assertCount(1, $broadcastsResponse);
        $this->assertEquals('b010t150', $broadcastsResponse['broadcasts'][0]['programme']['pid']);
        $this->assertEquals('b010t19z', $broadcastsResponse['broadcasts'][0]['programme']['programme']['pid']);
    }

    private function mockCollapsedBroadcastsService()
    {
        $mock =  $this->createMock('BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService');
        $mock->expects($this->once())
            ->method('findPastByProgramme')
            ->with($this->callback($this->isProgrammeWithPidFn('b010t150')))
            ->willReturn([
                 $this->createMockProgrammeWithParent(['b010t19z', 'b010t150']),
            ]);

        return $mock;
    }

    private function createMockProgrammeWithParent(array $pidAncestry)
    {
        $mockService = $this->createMock('BBC\ProgrammesPagesService\Domain\Entity\Service');
        $mockService->method('getNetwork')->willReturn(
            $this->createMock('BBC\ProgrammesPagesService\Domain\Entity\Network')
        );

        // create a programme with a parent programme field
        $mockParentProgramme = $this->createMock('BBC\ProgrammesPagesService\Domain\Entity\Episode');
        $mockParentProgramme->method('getPid')->willReturn(new Pid($pidAncestry[0]));
        $mockParentProgramme->method('getFirstBroadcastDate')->willReturn(new DateTimeImmutable());

        $mockChildProgramme = $this->createMock('BBC\ProgrammesPagesService\Domain\Entity\Episode');
        $mockChildProgramme->method('getPid')->willReturn(new Pid($pidAncestry[1]));
        $mockChildProgramme->method('getParent')->willReturn($mockParentProgramme);
        $mockChildProgramme->method('getFirstBroadcastDate')->willReturn(new DateTimeImmutable());

        $mockCollapsedBroadcast = $this->createMock('BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast');
        $mockCollapsedBroadcast->method('getProgrammeItem')->willReturn($mockChildProgramme);
        $mockCollapsedBroadcast->method('getServices')->willReturn([$mockService]);
        $mockCollapsedBroadcast->method('getStartAt')->willReturn(new DateTimeImmutable());
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
