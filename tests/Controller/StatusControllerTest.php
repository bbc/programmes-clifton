<?php

namespace Tests\BBC\CliftonBundle\Controller;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
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
        // Create mock service to throw exception and inject into container
        $mockService = $this->createMockProgrammesService();
        $mockService->expects($this->once())
            ->method('findByPidFull')
            ->with(new Pid('b006m86d'))
            ->willThrowException(new DBALException("Something bad happened."));
        $client->getContainer()->set('pps.programmes_service', $mockService);

        $client->request('GET', '/status');

        $this->assertResponseStatusCode($client, 500);
        $this->assertEquals('ERROR', $client->getResponse()->getContent());
    }

    public function testConnectionDBErrorFromElb()
    {
        $client = static::createClient([], [
            'HTTP_USER_AGENT' => 'ELB-HealthChecker/1.0',
        ]);
        // Create mock service to throw exception and inject into container
        $mockService = $this->createMockProgrammesService();
        $mockService->expects($this->once())
            ->method('findByPidFull')
            ->with(new Pid('b006m86d'))
            ->willThrowException(new ConnectionException("Cannot Connect."));
        $client->getContainer()->set('pps.programmes_service', $mockService);

        $client->request('GET', '/status');

        $this->assertResponseStatusCode($client, 200);
        $this->assertEquals('OK', $client->getResponse()->getContent());
    }

    private function createMockProgrammesService()
    {
        return $this->createMock(ProgrammesService::class);
    }
}
