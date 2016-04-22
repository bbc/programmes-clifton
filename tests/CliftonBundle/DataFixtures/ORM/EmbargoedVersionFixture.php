<?php

namespace Tests\BBC\CliftonBundle\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Version;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

class EmbargoedVersionFixture extends AbstractFixture
{
    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        // We want to prove that embargoed items don't get returned when querying
        $this->buildVersion('v0000000', true);
        $this->buildVersion('v0000001');
        $this->buildVersion('v0000002');

        $manager->flush();
    }

    private function buildVersion($pid, $embargoed = false)
    {
        $entity = new Version($pid);
        $entity->setIsEmbargoed($embargoed);
        $this->manager->persist($entity);
        return $entity;
    }
}
