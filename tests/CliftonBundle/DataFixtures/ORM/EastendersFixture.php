<?php

namespace Tests\BBC\CliftonBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Brand;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Episode;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Clip;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Series;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Image;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\RelatedLink;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\PartialDate;
use DateTime;

class EastendersFixture extends BaseFixture
{
    public function load(ObjectManager $manager)
    {
        $image = new Image('p01m5mss', 'Image Title');
        $image->setShortSynopsis('Image Synopsis');
        $image->setType('standard');
        $image->setExtension('jpg');

        $brand = new Brand('b006m86d', 'Eastenders');
        $brand->setAvailableClipsCount(2);
        $brand->setRelatedLinksCount(2);

        $series = new Series('b006m86f', 'Eastenders Series');
        $series->setAvailableClipsCount(2);
        $series->setParent($brand);

        $episode = new Episode('b06khpp0', '22/10/2015');
        $episode->setShortSynopsis('Short Synopsis');
        $episode->setReleaseDate(new PartialDate('2015-01'));
        $episode->setParent($series);
        $episode->setImage($image);
        $episode->setPosition(1);
        $episode->setStreamable(true);
        $episode->setStreamableUntil(new DateTime('2016-08-15T00:00:00Z'));
        $episode->setAvailableClipsCount(1);

        $episode2 = new Episode('b06khpp1', '25/11/2015');
        $episode2->setShortSynopsis('Short Synopsis');
        $episode2->setReleaseDate(new PartialDate('2015-11-25'));
        $episode2->setParent($series);
        $episode2->setPosition(2);
        $episode2->setStreamable(true);
        $episode2->setStreamableUntil(new DateTime('2016-08-15T00:00:00Z'));
        $episode2->setAvailableClipsCount(1);

        $episode3 = new Episode('b06khpp2', '27/11/2015');
        $episode3->setShortSynopsis('Short Synopsis');
        $episode3->setReleaseDate(new PartialDate('2015-11-27'));
        $episode3->setParent($series);
        $episode3->setPosition(3);
        $episode3->setStreamable(true);
        $episode3->setStreamableUntil(new DateTime('2016-08-15T00:00:00Z'));
        $episode3->setAvailableClipsCount(1);


        $clip = new Clip('b06khpq0', 'DummyClip');
        $clip->setShortSynopsis('Short Synopsis');
        $clip->setParent($episode);

        $clip2 = new Clip('b06khpq1', 'Another Dummy Clip');
        $clip2->setShortSynopsis('Short Synopsis');
        $clip2->setParent($episode2);


        $version = new Version('b06khpr0', $clip);
        $version2 = new Version('b06khpr1', $clip);

        $relatedLink = new RelatedLink('b06khps1', 'RL1', 'http://example.com', 'related_site', $brand, true);
        $relatedLink2 = new RelatedLink('b06khps2', 'RL1', 'http://example.com', 'standard', $brand, true);

        foreach ([
                     $image,
                     $brand,
                     $series,
                     $episode,
                     $episode2,
                     $episode3,
                     $clip,
                     $clip2,
                 ] as $entity) {
            $manager->persist($entity);
        }
        $manager->flush();
        foreach ([
                     $version,
                     $version2,
                     $relatedLink,
                     $relatedLink2,
                 ] as $entity) {
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
