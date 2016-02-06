<?php

namespace Tests\BBC\CliftonBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Status;
use DateTime;

class SimpleStatusFixture extends BaseFixture
{
    public function load(ObjectManager $manager)
    {
        $status = new Status();

        $status->setLatestChangeEventId('1234');
        $status->setLatestChangeEventCreatedAt(new DateTime('2015-11-25T00:01:00'));
        $status->setLatestChangeEventProcessedAt(new DateTime('2015-11-25T00:10:00'));
        $status->setPipsLatestId('56789');
        $status->setPipsLatestTime(new DateTime('2015-11-25T00:00:00'));

        $manager->persist($status);
        $manager->flush();
    }
}
