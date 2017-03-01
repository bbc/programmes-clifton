<?php

namespace Tests\BBC\CliftonBundle\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Broadcast;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Version;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use DateTime;

class CollapsedBroadcastsFixture extends AbstractFixture implements DependentFixtureInterface
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

        // build broadcasts
        $this->buildBroadcast($episode1, [1], '2015-08-01 00:00:00', '2015-08-01 01:00:00');
        $this->buildBroadcast($episode1, [1], '2015-08-02 00:00:00', '2015-08-02 01:00:00');
        $this->buildBroadcast($episode1, [2], '2015-09-03 00:00:00', '2015-09-03 01:00:00');
        $this->buildBroadcast($episode1, [1], '2015-10-01 00:00:00', '2015-10-01 01:00:00');
        $this->buildBroadcast($episode1, [1], '2016-01-18 00:00:00', '2015-01-18 01:00:00');
        $this->buildBroadcast($episode2Embargoed, [1], '2016-01-20 00:00:00', '2015-01-20 01:00:00');
        $this->buildBroadcast($episode2Embargoed, [1], '2016-03-03 00:00:00', '2015-03-03 01:00:00');

        // build webcasts
        $this->buildBroadcast($episode1, [], '2016-07-02 00:00:00', '2016-07-02 01:00:00', true);

        $this->manager->flush();
    }

    private function buildBroadcast($episode, $serviceIds, $start, $end, $isWebcastOnly = false)
    {
        $entity = new CollapsedBroadcast(
            $episode,
            '1',
            implode(',', $serviceIds),
            ($isWebcastOnly ? '1' : '0'),
            new \DateTime($start),
            new \DateTime($end)
        );
        $this->manager->persist($entity);
        return $entity;
    }
}
