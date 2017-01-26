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
    /**
     * @dataProvider validRoutesProvider
     */
    public function testCategoryMetadataValidRoutes($url, $pipId)
    {
        $this->loadFixtures(['MongrelsWithCategoriesFixture']);

        $broadcastsService = $this->mockCollapsedBroadcastsService();

        $broadcastsService->expects($this->once())->method('countByCategoryAndEndAtDateRange')
            ->willReturn(2);

        $client = static::createClient();
        // Inject the mock Service
        static::$kernel->getContainer()->set('pps.collapsed_broadcasts_service', $broadcastsService);
        $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertEquals($pipId, $jsonContent['category_page']['category']['id']);
    }

    public function validRoutesProvider()
    {
        return [
            ['/aps/programmes/genres/comedy.json', 'C00193'],
            ['/aps/programmes/genres/comedy/sitcoms.json', 'C00196'],
            ['/aps/programmes/genres/comedy/sitcoms/puppetysitcoms.json', 'C00999'],
            ['/aps/tv/programmes/genres/comedy/sitcoms/puppetysitcoms.json', 'C00999'],
            ['/aps/programmes/formats/animation.json', 'PT001'],
        ];
    }

    /**
     * @dataProvider invalidRoutesProvider
     */
    public function testCategoryMetadataInvalidRoutes($url)
    {
        $this->loadFixtures(['MongrelsWithCategoriesFixture']);

        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 404);
    }

    public function invalidRoutesProvider()
    {
        return [
            ['/aps/programmes/genres/comedy/sitcoms/puppetysitcoms/extralevel.json'],
            ['/aps/programmes/genres/notinthere.json'],
            ['/aps/programmes/format/with/2levels.json'],
            ['/aps/microwave/programmes/genres/comedy.json'],
        ];
    }

    public function testCategoryMetadataWithoutMedium()
    {
        $this->loadFixtures(['MongrelsWithCategoriesFixture']);

        $broadcastsService = $this->mockCollapsedBroadcastsService();

        $broadcastsService->expects($this->once())->method('countByCategoryAndEndAtDateRange')
            ->willReturn(2);

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
    }

    public function testCategoryMetadataWithMedium()
    {
        $this->loadFixtures(['MongrelsWithCategoriesFixture']);

        $broadcastsService = $this->mockCollapsedBroadcastsService();

        $broadcastsService->expects($this->once())->method('countByCategoryAndEndAtDateRange')
            ->willReturn(2);

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
