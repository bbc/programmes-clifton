<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;

use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\Entity\Contributor;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\CliftonBundle\ApsMapper\FindByPidVersionMapper;
use DateTimeImmutable;
use PHPUnit_Framework_TestCase;

class FindByPidVersionMapperAdditionalHydrationTest extends PHPUnit_Framework_TestCase
{
    public function testMappingContributions()
    {
        $version = $this->createMock(Version::CLASS);
        $version->method('getProgrammeItem')->willReturn($this->createMock(Episode::CLASS));

        $contributor = new Contributor(
            0,
            new Pid('p02z9mdz'),
            'person',
            'Benjamin Fry',
            null
        );

        $contribution = new Contribution(
            new Pid('p02zc3p4'),
            $contributor,
            'Presenter',
            1,
            null
        );

        $expectedContributors = [
            (object) [
                'character_name' => null,
                'name' => 'Benjamin Fry',
                'family_name' => '',
                'given_name' => '',
                'role' => 'Presenter',
            ],
        ];

        $mapper = new FindByPidVersionMapper();
        $apsObject = $mapper->getApsObject($version, [$contribution]);

        $this->assertObjectHasAttribute('contributors', $apsObject);
        $this->assertEquals($expectedContributors, $apsObject->contributors);
    }
}
