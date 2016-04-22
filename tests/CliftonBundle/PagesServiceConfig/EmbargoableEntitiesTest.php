<?php

namespace Tests\BBC\CliftonBundle\PagesServiceConfig;

use Tests\BBC\CliftonBundle\BaseWebTestCase;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\EntityRepository\CoreEntityRepository;

/**
 * @coversNone
 */
class EmbargoableEntitiesTest extends BaseWebTestCase
{
    public function testEmbargoedCoreEntitiesAreFilteredOut()
    {
        $this->loadFixtures(['EmbargoedProgrammeFixture']);
        $repo = $this->getContainer()->get('doctrine')->getRepository('ProgrammesPagesService:CoreEntity');

        // We do not expect to see the pid 99999999 or 00000000 which belong to the embargoed programmes
        $expectedPids = ['b017j7vs', 'b01777fr', 'b017j5jw'];

        $entities = $repo->findAllWithParents(10, 0);
        $this->assertEquals($expectedPids, array_column($entities, 'pid'));

        $this->assertEquals(3, $repo->countAll());

        // the parent was embargoed, so should not be accessible
        $this->assertTrue(isset($entities[1]['parent']));
        $this->assertFalse(isset($entities[2]['parent']));
    }

    public function testEmbargoedVersionsAreFilteredOut()
    {
        $this->loadFixtures(['EmbargoedVersionFixture']);
        $repo = $this->getContainer()->get('doctrine')->getRepository('ProgrammesPagesService:Version');

        $entities = $repo->findAll();
        $this->assertEquals('v0000001', $entities[0]->getPid());
        $this->assertEquals('v0000002', $entities[1]->getPid());
        // We do not expect to see the pid v0000000 which belongs to the embargoed version

        $this->assertEmpty($repo->findBy(['pid' => 'v0000000']));
    }
}
