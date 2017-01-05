<?php

namespace Tests\BBC\CliftonBundle\DataFixtures\ORM;

use DateTime;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Broadcast;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Version;

class BroadcastsWithCategoriesFixture extends AbstractFixture implements DependentFixtureInterface
{

    private $manager;

    public function getDependencies()
    {
        return [
            NetworksFixture::class,
            TleosByCategoryFixture::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        // get episodes
        $ep12 = $this->getReference('b01777fa');
        $ep22 = $this->getReference('b01777fb');
        $ep13 = $this->getReference('b017j555');
        $ep23 = $this->getReference('b017j556');
        $ep33 = $this->getReference('b017j557');

        $service = $this->getReference('p00fzl7j');

        // build versions
        $version1 = $this->buildVersion('v0000011', $ep12);
        $version2 = $this->buildVersion('v0000013', $ep22);
        $version3 = $this->buildVersion('v0000014', $ep13);
        $version4 = $this->buildVersion('v0000015', $ep23);
        $version5 = $this->buildVersion('v0000016', $ep33);

        $this->manager->flush();

        // build broadcasts
        $this->buildBroadcast('brdcst001', $version1, $service, '2016-10-04 00:00:10', '2016-10-04 10:01:10');
        $this->buildBroadcast('brdcst002', $version2, $service, '2016-10-04 00:00:11', '2016-10-04 10:02:11');
        $this->buildBroadcast('brdcst004', $version3, $service, '2016-10-04 00:00:12', '2016-10-04 12:03:12');
        $this->buildBroadcast('brdcst005', $version4, $service, '2016-10-05 00:00:13', '2016-10-04 20:04:13');
        $this->buildBroadcast('brdcst009', $version5, $service, '2016-10-05 00:01:14', '2016-10-04 23:05:14');

        $this->manager->flush();
    }

    private function buildBroadcast($pid, $version, $service, $start, $end)
    {
        $entity = new Broadcast($pid, $version, new DateTime($start), new DateTime($end));
        $entity->setProgrammeItem($version->getProgrammeItem());
        $entity->setService($service);
        $entity->setIsWebcast(is_null($service));
        $this->manager->persist($entity);
        return $entity;
    }

    private function buildVersion($pid, $episode)
    {
        $entity = new Version($pid, $episode);
        $this->manager->persist($entity);
        return $entity;
    }
}
