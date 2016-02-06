<?php

namespace Tests\BBC\CliftonBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Brand;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Episode;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Clip;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Series;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Image;
use BBC\ProgrammesPagesService\Domain\ValueObject\PartialDate;
use DateTime;

class EastendersFixture extends BaseFixture
{
    public function load(ObjectManager $manager)
    {
        $image = new Image();
        $image->setPid('p01m5mss');
        $image->setTitle('Image Title');
        $image->setShortSynopsis('Image Synopsis');
        $image->setType('standard');
        $image->setExtension('jpg');

        $brand = new Brand();
        $brand->setPid('b006m86d');
        $brand->setTitle('Eastenders');
        $brand->setReleaseDate(new PartialDate('2015'));
        $brand->setAvailableClipsCount(2);

        $series = new Series();
        $series->setPid('b006m86f');
        $series->setTitle('Eastenders Series');
        $series->setReleaseDate(new PartialDate('2015-01'));
        $series->setAvailableClipsCount(2);
        $series->setParent($brand);

        $episode = new Episode();
        $episode->setPid('b06khpp0');
        $episode->setTitle('22/10/2015');
        $episode->setShortSynopsis('Short Synopsis');
        $episode->setParent($series);
        $episode->setImage($image);

        $episode->setIsStreamable(true);
        $episode->setStreamableUntil(new DateTime('2016-08-15T00:00:00Z'));
        $episode->setAvailableClipsCount(1);

        $episode2 = new Episode();
        $episode2->setPid('b06khpp1');
        $episode2->setTitle('25/11/2015');
        $episode->setShortSynopsis('Short Synopsis');
        $episode2->setParent($series);
        $episode2->setIsStreamable(true);
        $episode2->setStreamableUntil(new DateTime('2016-08-15T00:00:00Z'));
        $episode2->setAvailableClipsCount(1);


        $clip = new Clip();
        $clip->setPid('b06khpq0');
        $clip->setTitle('DummyClip');
        $episode->setShortSynopsis('Short Synopsis');
        $clip->setParent($episode);

        $clip2 = new Clip();
        $clip2->setPid('b06khpq1');
        $clip2->setTitle('Another Dummy Clip');
        $episode->setShortSynopsis('Short Synopsis');
        $clip2->setParent($episode2);

        foreach ([$image, $brand, $series, $episode, $episode2, $clip, $clip2] as $entity) {
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
