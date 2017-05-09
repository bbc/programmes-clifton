<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\Entity\VersionType;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\CliftonBundle\ApsMapper\FindByPidVersionMapper;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FindByPidVersionMapperTest extends TestCase
{
    public function testMappingVersion()
    {
        $episode = $this->createMock(Episode::CLASS);
        $episode->method('getPid')->willReturn(new Pid('b007733d'));
        $episode->method('getTitle')->willReturn('19/01/2007');

        $versionType = new VersionType('Original', 'Original version');

        $version = new Version(
            0,
            new Pid('b006qn42'),
            $episode,
            false,
            false,
            0,
            22,
            101,
            'GuidanceWarnings',
            true,
            null,
            null,
            [$versionType]
        );

        $expectedOutput = (object) [
            'canonical' => '1',
            'pid' => 'b006qn42',
            'duration' => 101,
            'parent' => (object) [
                'programme' => (object) [
                    'type' => 'episode',
                    'pid' => 'b007733d',
                    'title' => '19/01/2007',
                ],
            ],
            'types' => [
                "Original version",
            ],
            'contributors' => [],
            'segment_events' => [],
            'broadcasts' => [],
            'availabilities' => [],
        ];

        $mapper = new FindByPidVersionMapper();
        $this->assertEquals($expectedOutput, $mapper->getApsObject($version));
    }

    public function testMappingNonCanonicalVersion()
    {
        $version = $this->createMock(Version::CLASS);
        $version->method('getProgrammeItem')->willReturn($this->createMock(Episode::CLASS));
        $version->method('getVersionTypes')->willReturn([new VersionType('audio_described', 'Audio Described')]);


        $mapper = new FindByPidVersionMapper();
        $apsObject = $mapper->getApsObject($version);

        $this->assertSame(0, $apsObject->canonical);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidDomainObject()
    {
        $episode = $this->createMock(Episode::CLASS);

        $mapper = new FindByPidVersionMapper();
        $mapper->getApsObject($episode);
    }
}
