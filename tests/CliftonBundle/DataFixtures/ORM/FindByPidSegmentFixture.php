<?php

namespace Tests\BBC\CliftonBundle\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Segment;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class FindByPidSegmentFixture extends AbstractFixture
{
    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->buildMusicSegment(
            'p002d8dd',
            'music',
            'Hustler\'s Anthem',
            268,
            'n2bnxw',
            'Universal',
            'C'
        );

        $this->buildSegment(
            'p00wx0df',
            'chapter',
            'Much Ado About Nothing',
            334,
            'Bidisha reviews Much Ado About Nothing starring Meera Syal'
        );

        $this->manager->flush();
    }

    private function buildMusicSegment($pid, $type, $title, $duration, $musicRecordId, $publisher, $musicCode)
    {
        $segment = new Segment($pid, $type);
        $segment->setTitle($title);
        $segment->setDuration($duration);
        $segment->setMusicRecordId($musicRecordId);
        $segment->setPublisher($publisher);
        $segment->setMusicCode($musicCode);

        $this->manager->persist($segment);
        $this->addReference($pid, $segment);

        return $segment;
    }

    private function buildSegment($pid, $type, $title, $duration, $shortSynopsis)
    {
        $segment = new Segment($pid, $type);
        $segment->setTitle($title);
        $segment->setDuration($duration);
        $segment->setShortSynopsis($shortSynopsis);

        $this->manager->persist($segment);
        $this->addReference($pid, $segment);

        return $segment;
    }
}
