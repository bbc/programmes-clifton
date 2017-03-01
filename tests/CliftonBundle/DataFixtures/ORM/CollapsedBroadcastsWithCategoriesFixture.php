<?php

namespace Tests\BBC\CliftonBundle\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\CollapsedBroadcast;
use DateTime;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Broadcast;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Version;

class CollapsedBroadcastsWithCategoriesFixture extends AbstractFixture implements DependentFixtureInterface
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

        $this->manager->flush();

        // build broadcasts
        $this->buildBroadcast($ep12, [1], '2016-10-04 00:00:10', '2016-10-04 10:01:10');
        $this->buildBroadcast($ep22, [1], '2016-10-04 00:00:11', '2016-10-04 10:02:11');
        $this->buildBroadcast($ep13, [1], '2016-10-04 00:00:12', '2016-10-04 12:03:12');
        $this->buildBroadcast($ep23, [1], '2016-10-05 00:00:13', '2016-10-04 20:04:13');
        $this->buildBroadcast($ep33, [1], '2016-10-05 00:01:14', '2016-10-04 23:05:14');

        $this->manager->flush();
    }

    private function buildBroadcast($episode, $serviceIds, $start, $end)
    {
        $entity = new CollapsedBroadcast(
            $episode,
            '1',
            implode(',', $serviceIds),
            '0',
            new \DateTime($start),
            new \DateTime($end)
        );
        $this->manager->persist($entity);
        return $entity;
    }
}
