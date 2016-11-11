<?php

namespace Tests\BBC\CliftonBundle\DataFixtures\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Genre;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Format;

class MongrelsWithCategoriesFixture extends BaseFixture implements DependentFixtureInterface
{
    private $manager;

    public function getDependencies()
    {
        return [
            MongrelsFixture::CLASS,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $category1 = $this->buildGenre('C00193', 'Comedy', 'comedy');
        $category2 = $this->buildGenre('C00196', 'Sitcoms', 'sitcoms', $category1);
        $category3 = $this->buildGenre('C00999', 'Puppety Sitcoms', 'puppetysitcoms', $category2);
        $format1 = $this->buildFormat('PT001', 'Animation', 'animation');

        $brand = $this->getReference('b010t19z');
        $brand->setCategories(new ArrayCollection([$category2, $category3]));
        $manager->persist($brand);

        $brand = $this->getReference('b00swgkn');
        $brand->setCategories(new ArrayCollection([$category1, $category2, $format1]));
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
}
