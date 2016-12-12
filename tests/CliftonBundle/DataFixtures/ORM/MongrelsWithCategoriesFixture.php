<?php

namespace Tests\BBC\CliftonBundle\DataFixtures\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Genre;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\MasterBrand;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Format;

class MongrelsWithCategoriesFixture extends BaseFixture implements DependentFixtureInterface
{
    private $manager;

    public function getDependencies()
    {
        return [
            MongrelsFixture::CLASS,
            NetworksFixture::CLASS,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $category1 = $this->buildGenre('C00193', 'Comedy', 'comedy');
        $category2 = $this->buildGenre('C00196', 'Sitcoms', 'sitcoms', $category1);
        $category3 = $this->buildGenre('C00999', 'Puppety Sitcoms', 'puppetysitcoms', $category2);
        $category4 = $this->buildGenre('C01000', 'British Sitcoms', 'britishsitcoms', $category2);
        $format1 = $this->buildFormat('PT001', 'Animation', 'animation');

        $brand = $this->getReference('b010t19z');
        $brand->setCategories(new ArrayCollection([$category2, $category3]));
        $manager->persist($brand);

        // Set medium to TV
        $s2e1 = $this->getReference('b0175lqm');
        $network = $this->getReference('network_bbc_one');
        $masterBrand = $this->buildMasterBrand('bbc_one_london', 'p01y7bvv', 'BBC One London');
        $masterBrand->setNetwork($network);
        $s2e1->setMasterBrand($masterBrand);
        $s2e1->setStreamable(true);
        $s2e1->setCategories(new ArrayCollection([$category3, $format1]));
        $manager->persist($s2e1);

        $brand = $this->getReference('b00swgkn');
        $brand->setCategories(new ArrayCollection([$category1, $category2]));
        $manager->persist($brand);

        $manager->flush();
    }

    private function buildGenre($pidId, $title, $urlKey, $parent = null)
    {
        $entity = new Genre($pidId, $title, $urlKey);
        $entity->setParent($parent);
        $this->manager->persist($entity);
        return $entity;
    }

    private function buildFormat($pidId, $title, $urlKey)
    {
        $entity = new Format($pidId, $title, $urlKey);
        $this->manager->persist($entity);
        return $entity;
    }

    private function buildMasterBrand($mid, $pid, $name)
    {
        $entity = new MasterBrand($mid, $pid, $name);
        $this->manager->persist($entity);
        $this->addReference($mid, $entity);
        return $entity;
    }
}
