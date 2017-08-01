<?php

namespace Tests\BBC\CliftonBundle\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\AtozTitle;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Brand;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Clip;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Episode;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\MasterBrand;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Network;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Series;
use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\BBC\CliftonBundle\DataFixtures\ORM\BaseFixture;

class AtozTitleFixture extends BaseFixture
{
    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $network = $this->buildNetwork('bbc_one', 'BBC One', NetworkMediumEnum::TV);
        $masterBrand = $this->buildMasterBrand('bbc_one', 'c0000000', 'BBC One', $network);
        $brandTleo = $this->buildProgramme(Brand::class, 'b010t19z', 'Mongrels', $masterBrand, true);
        $brandTleoTitle = $this->buildAtozTitle($brandTleo->getTitle(), $brandTleo);

        $network2 = $this->buildNetwork('radio_one', 'Radio One', NetworkMediumEnum::RADIO);
        $masterBrand2 = $this->buildMasterBrand('radio_one', 'c0000001', 'Radio One', $network2);
        $brandTleo2 = $this->buildProgramme(Brand::class, 'b0020020', 'Mmmmm, unit tests', $masterBrand2, true);
        $brandTleoTitle2 = $this->buildAtozTitle($brandTleo2->getTitle(), $brandTleo2);

        $seriesTleo = $this->buildProgramme(Series::class, 'b0000001', 'The WibbleTron2000');
        $seriesTleoTitle1 = $this->buildAtozTitle($seriesTleo->getTitle(), $seriesTleo);
        $seriesTleoTitle2 = $this->buildAtozTitle('WibbleTron2000, The', $seriesTleo);

        $episodeTleo = $this->buildProgramme(Episode::class, 'b0000002', '3000UberWibbleTron3000', null, true);
        $episodeTleoTitle = $this->buildAtozTitle($episodeTleo->getTitle(), $episodeTleo);

        $embargoedTleo = $this->buildProgramme(Brand::class, 'b0000004', 'Prince Harry\'s death rattle', null, false, null, null, true);
        $embargoedTleoTitle = $this->buildAtozTitle($embargoedTleo->getTitle(), $embargoedTleo);

        $clipTleo = $this->buildProgramme(Clip::class, 'b0000003', 'The Best of McWibbleTron');

        $series1 = $this->buildProgramme(Series::class, 'b00swyx1', 'Series 1', null, false, $brandTleo, 1);

        $s1e1 = $this->buildProgramme(Episode::class, 'b00swgkn', 'Episode 1', null, false, $series1, 1);

        $manager->flush();
    }

    private function buildProgramme(
        $type,
        $pid,
        $title,
        $masterBrand = null,
        $streamable = false,
        $parent = null,
        $position = null,
        $isEmbargoed = false
    ) {
        $entity = new $type($pid, $title);
        $entity->setMasterBrand($masterBrand);
        $entity->setStreamable($streamable);
        $entity->setParent($parent);
        $entity->setPosition($position);
        $entity->setIsEmbargoed($isEmbargoed);
        $this->manager->persist($entity);
        return $entity;
    }

    private function buildAtozTitle($title, $coreEntity)
    {
        $entity = new AtozTitle($title, $coreEntity);
        $this->manager->persist($entity);
        return $entity;
    }

    private function buildNetwork($nid, $title, $medium = null)
    {
        $entity = new Network($nid, $title);
        $entity->setMedium($medium);
        $this->manager->persist($entity);
        return $entity;
    }

    private function buildMasterBrand($mid, $pid, $name, $network = null)
    {
        $entity = new MasterBrand($mid, $pid, $name);
        $entity->setNetwork($network);
        $this->manager->persist($entity);
        return $entity;
    }
}
