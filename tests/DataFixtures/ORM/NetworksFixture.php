<?php

namespace Tests\BBC\CliftonBundle\DataFixtures\ORM;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Network;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Service;
use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

class NetworksFixture extends AbstractFixture
{
    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $service = $this->buildService(
            'bbc_radio_fourfm',
            'p00fzl7j',
            'Radio Four FM',
            'National Radio',
            'audio'
        );

        $this->buildNetwork(
            'bbc_radio_four',
            'BBC Radio Four',
            $service,
            'radio4'
        );

        $service2 = $this->buildService(
            'bbc_radio_two',
            'p00fzl8v',
            'Radio 2',
            'National Radio',
            'audio'
        );

        $service3 = $this->buildService(
            'bbc_one_london',
            'p00fzl6p',
            'BBC One London',
            'National TV',
            'audio_video'
        );

        $this->buildNetwork(
            'bbc_radio_two',
            'BBC Radio 2',
            $service2,
            'radio2'
        );

        $network = $this->buildNetwork(
            'bbc_one',
            'BBC One',
            $service3,
            'bbcone',
            NetworkMediumEnum::TV
        );

        $service3->setNetwork($network);

        $this->manager->flush();
    }

    private function buildService(
        $sid,
        $pid,
        $title,
        $type,
        $mediaType
    ) {
        $entity = new Service($sid, $pid, $title, $type, $mediaType);
        $this->manager->persist($entity);
        $this->addReference($pid, $entity);
        return $entity;
    }

    private function buildNetwork(
        $nid,
        $title,
        $defaultService = null,
        $urlKey = null,
        $medium = null
    ) {
        $entity = new Network($nid, $title);
        $entity->setDefaultService($defaultService);
        $entity->setUrlKey($urlKey);
        if ($medium) {
            $entity->setMedium($medium);
        }
        $this->manager->persist($entity);
        $this->addReference('network_' . $nid, $entity);
        return $entity;
    }
}
