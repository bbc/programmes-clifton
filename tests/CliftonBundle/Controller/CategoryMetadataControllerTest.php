<?php

namespace Tests\BBC\CliftonBundle\Controller;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use DateTimeImmutable;
use Tests\BBC\CliftonBundle\BaseWebTestCase;

/**
 * This class includes mocking of services, instead of relying entirely on
 * database fixtures for a full end-to-end test. This is because some of the
 * service calls make calls to MySQL-specific queries which do not work in
 * SQLite.
 */
class CategoryMetadataControllerTest extends BaseWebTestCase
{
    public function testCategoryMetadataWithoutMedium()
    {
        $this->loadFixtures(['MongrelsWithCategoriesFixture']);

        $broadcastsService = $this->mockCollapsedBroadcastsService();

        $broadcastsService->expects($this->once())->method('countByCategoryAndEndAtDateRange')
            ->willReturn(2);

        $broadcastsService->expects($this->once())->method('findByCategoryAndEndAtDateRange')
            ->willReturn([$this->createMockBroadcast('p0000001'), $this->createMockBroadcast('p0000002')]);

        $client = static::createClient();
        // Inject the mock Service
        static::$kernel->getContainer()->set('pps.collapsed_broadcasts_service', $broadcastsService);
        $client->request('GET', '/aps/programmes/genres/comedy.json');

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertArrayHasKey('category_page', $jsonContent);
        $this->assertArrayHasKey('category', $jsonContent['category_page']);
        $this->assertArrayHasKey('available_programmes_count', $jsonContent['category_page']);
        $this->assertArrayHasKey('available_programmes', $jsonContent['category_page']);
        $this->assertArrayHasKey('upcoming_broadcasts_count', $jsonContent['category_page']);
        $this->assertArrayHasKey('upcoming_broadcasts', $jsonContent['category_page']);
        $this->assertArrayHasKey('available_and_upcoming_counts', $jsonContent['category_page']);

        $this->assertEquals('C00193', $jsonContent['category_page']['category']['id']);
        $this->assertEquals('b0175lqm', $jsonContent['category_page']['available_programmes'][0]['pid']);
    }
    public function testCategoryMetadataWithMedium()
    {
        $this->loadFixtures(['MongrelsWithCategoriesFixture']);

        $broadcastsService = $this->mockCollapsedBroadcastsService();

        $broadcastsService->expects($this->once())->method('countByCategoryAndEndAtDateRange')
            ->willReturn(2);

        $broadcastsService->expects($this->once())->method('findByCategoryAndEndAtDateRange')
            ->willReturn([$this->createMockBroadcast('p0000001'), $this->createMockBroadcast('p0000002')]);

        $client = static::createClient();
        // Inject the mock Service
        static::$kernel->getContainer()->set('pps.collapsed_broadcasts_service', $broadcastsService);
        $client->request('GET', '/aps/tv/programmes/genres/comedy/sitcoms/puppetysitcoms.json');

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertArrayHasKey('category_page', $jsonContent);
        $this->assertArrayHasKey('category', $jsonContent['category_page']);
        $this->assertArrayHasKey('available_programmes_count', $jsonContent['category_page']);
        $this->assertArrayHasKey('available_programmes', $jsonContent['category_page']);
        $this->assertArrayHasKey('upcoming_broadcasts_count', $jsonContent['category_page']);
        $this->assertArrayHasKey('upcoming_broadcasts', $jsonContent['category_page']);
        $this->assertArrayHasKey('service', $jsonContent['category_page']);
        $this->assertArrayHasKey('available_and_upcoming_counts', $jsonContent['category_page']);

        $this->assertArrayHasKey('broader', $jsonContent['category_page']['category']);
        $this->assertEquals(1, count($jsonContent['category_page']['category']['broader']));

        $this->assertEquals('C00999', $jsonContent['category_page']['category']['id']);
        $this->assertEquals('b0175lqm', $jsonContent['category_page']['available_programmes'][0]['pid']);
    }

    public function testCategoryMetadataWithNonExistantCategory()
    {
        $this->loadFixtures([]);

        $client = static::createClient();
        $client->request('GET', '/aps/radio/programmes/wibble.json');

        $this->assertResponseStatusCode($client, 404);
    }

    private function isProgrammeWithPipIdFn($pipId)
    {
        return (function ($category) use ($pipId) {
            return $category->getId() == $pipId;
        });
    }

    private function mockCollapsedBroadcastsService()
    {
        return $this->createMock('BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService');
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
