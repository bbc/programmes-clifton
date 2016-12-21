<?php

namespace Tests\BBC\CliftonBundle\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Broadcast;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Version;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use DateTime;

class BroadcastsFixture extends AbstractFixture implements DependentFixtureInterface
{
    private $manager;

    public function getDependencies()
    {
        return [
            NetworksFixture::class,
            EmbargoedProgrammeFixture::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        // grab episode
        $episode1 = $this->getReference('b01777fr');
        $episode2Embargoed = $this->getReference('99999999');

        // grab service
        $service = $this->getReference('p00fzl7j');
        $service2 = $this->getReference('p00fzl6p');

        // build versions
        $version1 = $this->buildVersion('v0000011', $episode1);
        $version2Embargoed = $this->buildVersion('v0000012', $episode2Embargoed);

        // build broadcasts
        $this->buildBroadcast('brdcst001', $version1, $service, '2015-08-01 00:00:00', '2015-08-01 01:00:00');
        $this->buildBroadcast('brdcst002', $version1, $service, '2015-08-02 00:00:00', '2015-08-02 01:00:00');
        $this->buildBroadcast('brdcst003', $version1, $service2, '2015-09-03 00:00:00', '2015-09-03 01:00:00');
        $this->buildBroadcast('brdcst004', $version1, $service, '2015-10-01 00:00:00', '2015-10-01 01:00:00');
        $this->buildBroadcast('brdcst005', $version1, $service, '2016-01-18 00:00:00', '2015-01-18 01:00:00');
        $this->buildBroadcast('brdcst006', $version2Embargoed, $service, '2016-01-20 00:00:00', '2015-01-20 01:00:00');
        $this->buildBroadcast('brdcst007', $version2Embargoed, $service, '2016-03-03 00:00:00', '2015-03-03 01:00:00');

        // build webcasts
        $this->buildBroadcast('brdcst008', $version1, null, '2016-07-02 00:00:00', '2016-07-02 01:00:00');

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
