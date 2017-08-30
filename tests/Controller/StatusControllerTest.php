<?php

namespace Tests\BBC\CliftonBundle\Controller;

use BBC\ProgrammesPagesService\Service\BroadcastsService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use BBC\ProgrammesPagesService\Service\SegmentEventsService;
use BBC\ProgrammesPagesService\Service\VersionsService;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Tests\BBC\CliftonBundle\BaseWebTestCase;

class StatusControllerTest extends BaseWebTestCase
{
    public function testStatus()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/status');
        $this->assertResponseStatusCode($client, 200);

        $this->assertEquals('YES', $crawler->filter('[data-test-name=db-connectivity] span')->text());
    }

    public function testStatusFromElb()
    {
        $client = static::createClient([], [
            'HTTP_USER_AGENT' => 'ELB-HealthChecker/1.0',
        ]);
        $client->request('GET', '/status');

        $this->assertResponseStatusCode($client, 200);
        $this->assertEquals('OK', $client->getResponse()->getContent());
    }

    public function testNonConnectionDBErrorFromElb()
    {
        $client = static::createClient([], [
            'HTTP_USER_AGENT' => 'ELB-HealthChecker/1.0',
        ]);

        // clip mock
        $mockProgrammeService = $this->createMockProgrammesService();
        $mockProgrammeService->expects($this->once())->method('findByPidFull');
        $client->getContainer()->set('pps.programmes_service', $mockProgrammeService);

        // broadcast service mock
        $mockBroadcastService = $this->createMock(BroadcastsService::class);
        $mockBroadcastService->expects($this->once())->method('findByServiceAndDateRange');
        $client->getContainer()->set('pps.broadcasts_service', $mockBroadcastService);

        // version mock
        $mockVersionService = $this->createMock(VersionsService::class);
        $mockVersionService->expects($this->once())->method('findByPidFull');
        $client->getContainer()->set('pps.versions_service', $mockVersionService);

        // segment events mock. This one throw an exception and injects it into the container
        $mockSegmentEventsService = $this->createMock(SegmentEventsService::class);
        $mockSegmentEventsService->expects($this->once())
            ->method('findByPidFull')
            ->willThrowException(new DBALException("Something bad happened."));
        $client->getContainer()->set('pps.segment_events_service', $mockSegmentEventsService);

        $client->request('GET', '/status');

        $this->assertResponseStatusCode($client, 500);
        $this->assertEquals('ERROR', $client->getResponse()->getContent());
    }

    public function testConnectionDBErrorFromElb()
    {
        $client = static::createClient([], [
            'HTTP_USER_AGENT' => 'ELB-HealthChecker/1.0',
        ]);

        // clip mock
        $mockProgrammeService = $this->createMockProgrammesService();
        $mockProgrammeService->expects($this->once())->method('findByPidFull');
        $client->getContainer()->set('pps.programmes_service', $mockProgrammeService);

        // broadcast service mock
        $mockBroadcastService = $this->createMock(BroadcastsService::class);
        $mockBroadcastService->expects($this->once())->method('findByServiceAndDateRange');
        $client->getContainer()->set('pps.broadcasts_service', $mockBroadcastService);

        // version mock
        $mockVersionService = $this->createMock(VersionsService::class);
        $mockVersionService->expects($this->once())->method('findByPidFull');
        $client->getContainer()->set('pps.versions_service', $mockVersionService);

        // segment events mock. This one throw an exception and injects it into the container
        $mockSegmentEventsService = $this->createMock(SegmentEventsService::class);
        $mockSegmentEventsService->expects($this->once())
            ->method('findByPidFull')
            ->willThrowException(new ConnectionException("Cannot Connect."));
        $client->getContainer()->set('pps.segment_events_service', $mockSegmentEventsService);

        $client->request('GET', '/status');

        $this->assertResponseStatusCode($client, 200);
        $this->assertEquals('OK', $client->getResponse()->getContent());
    }

    private function createMockProgrammesService()
    {
        return $this->createMock(ProgrammesService::class);
    }
}
