<?php

namespace Tests\BBC\CliftonBundle\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Version;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Brand;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Episode;

class EmbargoedProgrammeFixture extends AbstractFixture
{
    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $brand = $this->buildBrand('b017j7vs', 'Old Jews Telling Jokes');
        $brand2 = $this->buildBrand('00000000', 'Old Jews Telling Jokes 2', true);

        $e1 = $this->buildEpisode('b01777fr', 'Episode 1', $brand);
        $e2 = $this->buildEpisode('b017j5jw', 'Episode 2', $brand2);

        // The mythical 3rd episode doesn't exist, but we want to prove that
        // embargoed items don't get returned when querying
        $e3Embargoed = $this->buildEpisode('99999999', 'Episode 3', $brand, true);

        $manager->flush();

        // add a version to the embargoed episode
        $version = new Version('v0000001', $e3Embargoed);
        $this->manager->persist($version);

        $manager->flush();
    }

    private function buildBrand($pid, $title, $embargoed = false)
    {
        $entity = new Brand($pid, $title);
        $entity->setIsEmbargoed($embargoed);
        $this->addReference($pid, $entity);
        $this->manager->persist($entity);
        return $entity;
    }

    private function buildEpisode($pid, $title, $parent = null, $embargoed = false)
    {
        $entity = new Episode($pid, $title);
        $entity->setParent($parent);
        $entity->setIsEmbargoed($embargoed);
        $this->addReference($pid, $entity);
        $this->manager->persist($entity);
        return $entity;
    }
}
