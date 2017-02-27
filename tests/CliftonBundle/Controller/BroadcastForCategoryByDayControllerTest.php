<?php

namespace Tests\BBC\CliftonBundle\Controller;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use DateTimeImmutable;
use Tests\BBC\CliftonBundle\BaseWebTestCase;

/**
 * @covers BBC\CliftonBundle\Controller\BroadcastForCategoryByDayController
 */
class BroadcastForCategoryByDayControllerTest extends BaseWebTestCase
{
    /**
     * @dataProvider listUrlsWithFoundResults
     */
    public function testShowBroadcastForGenresByDay($url)
    {
        // mock service in container
        $serviceMock = $this->createMock('BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService');
        $serviceMock->expects($this->once())
            ->method('findByCategoryAndStartAtDateRange')
            ->willReturn([
                 $this->createMockBroadcast('p0000001'),
                 $this->createMockBroadcast('p0000002'),
                 $this->createMockBroadcast('p0000003'),
             ]);

        $client = static::createClient();
        $client->getContainer()->set('pps.collapsed_broadcasts_service', $serviceMock);

        // call
        $client->request('GET', $url);

        // asserts
        $broadcastsItems = $this->getDecodedJsonContent($client);
        $this->assertResponseStatusCode($client, 200);
        $this->assertCount(3, $broadcastsItems['broadcasts']);
    }

    public function listUrlsWithFoundResults()
    {
        /**
         * Genres:
         *      - comedy
         *          - sitcoms
         *              - puppetsitComs
         *              - britishsitcoms
         *
         * Formats:
         *      - animation
         */
        return [
            // medium = null
            ["/aps/programmes/genres/comedy/schedules/2016/12/04.json"],
            ["/aps/programmes/genres/comedy/sitcoms/schedules/2016/12/04.json"],
            ["/aps/programmes/genres/comedy/sitcoms/puppetysitcoms/schedules/2016/12/04.json"],
            ["/aps/programmes/formats/animation/schedules/2016/12/04.json"],
        ];
    }

    protected function setUp()
    {
        // set categories and brands in test db
        $this->loadFixtures(['MongrelsWithCategoriesFixture']);
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
