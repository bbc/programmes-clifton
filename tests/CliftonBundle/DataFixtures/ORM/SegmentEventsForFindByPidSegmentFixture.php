<?php

namespace Tests\BBC\CliftonBundle\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Contribution;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\CreditRole;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\SegmentEvent;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Version;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SegmentEventsForFindByPidSegmentFixture extends AbstractFixture implements DependentFixtureInterface
{
    private $manager;

    public function getDependencies()
    {
        return [
            NetworksFixture::class,
            ContributorsFixture::class,
            FindByPidSegmentFixture::class,
            MongrelsFixture::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        // build contributors
        $contributor1 = $this->getReference('cntrbtr1');

        // build segments
        $segment1 = $this->getReference('p002d8dd');
        $segment2 = $this->getReference('p00wx0df');

        $role = new CreditRole('PERFORMER');
        $role->setName('Performer');

        $this->manager->persist($role);

        // build contributions
        $this->buildContribution(
            'cntrbtn1',
            $contributor1,
            $role,
            $segment1
        );

        // build episodes (with full hierarchy)
        $episode1 = $this->getReference('b00swgkn');
        $episode2 = $this->getReference('b00syxx6');

        // build versions
        $version1 = $this->buildVersion('v0000001', $episode1);
        $version2 = $this->buildVersion('v0000002', $episode2);

        // build the segment events
        $this->buildSegmentEvent('sv000001', $version1, $segment1);
        $this->buildSegmentEvent('sv000002', $version2, $segment2);

        $this->manager->flush();
    }

    private function buildContribution($pid, $contributor, $role, $segment)
    {
        $entity = new Contribution($pid, $contributor, $role, $segment);
        $this->manager->persist($entity);
        return $entity;
    }

    private function buildSegmentEvent($pid, $version, $segment)
    {
        $entity = new SegmentEvent($pid, $version, $segment);
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
