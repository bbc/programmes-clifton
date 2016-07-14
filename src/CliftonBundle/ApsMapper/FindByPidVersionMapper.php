<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\Entity\VersionType;
use InvalidArgumentException;
use stdClass;

class FindByPidVersionMapper implements MapperInterface
{
    use Traits\ProgrammeUtilitiesTrait;

    public function getApsObject($version, $contributions = [], $segmentEvents = [], $broadcasts = []): stdClass
    {
        /** @var Version $version */
        $this->assertIsVersion($version);

        $isCanonical = 0;
        foreach ($version->getVersionTypes() as $vt) {
            if ($vt->getType() == 'Original') {
                $isCanonical = 1;
                break;
            }
        }

        $output = [
            'canonical' => $isCanonical,
            'pid' => $version->getPid(),
            'duration' => $version->getDuration(),
            'parent' => $this->getParent($version->getProgrammeItem()),
            'types' => array_map(function (VersionType $vt) {
                return $vt->getName();
            }, $version->getVersionTypes()),
            'contributors' => array_map([$this, 'getContributor'], $contributions),
            'segment_events' => array_map([$this, 'getSegmentEvent'], $segmentEvents),
            'broadcasts' => array_map([$this, 'getBroadcast'], $broadcasts),
            'availabilities' => [],
        ];

        return (object) $output;
    }

    private function getParent(ProgrammeItem $programme)
    {
        $output = [
            'type' => $this->getProgrammeType($programme),
            'pid' => (string) $programme->getPid(),
            'title' => $this->getProgrammeTitle($programme),
        ];

        return (object) ['programme' => (object) $output];
    }

    private function getContributor(Contribution $contribution)
    {
        $output = [
            'character_name' => $contribution->getCharacterName() ?? null,
            'name' => $contribution->getContributor()->getName(),
            'family_name' => '',
            'given_name' => '',
            'role' => $contribution->getCreditRole(),
        ];

        $musicBrainzId = $contribution->getContributor()->getMusicBrainzId();
        if ($musicBrainzId) {
            $output['musicbrainz_gid'] = $musicBrainzId;
        }

        return (object) $output;
    }

    private function getSegmentEvent(SegmentEvent $segmentEvent)
    {
        return (object) [
            'pid' => $segmentEvent->getPid(),
        ];
    }

    private function getBroadcast(Broadcast $broadcast)
    {
        return (object) [
            'pid' => $broadcast->getPid(),
        ];
    }

    private function assertIsVersion($item)
    {
        if (!($item instanceof Version)) {
            throw new InvalidArgumentException(sprintf(
                'Entity should be an instance of "%s". Got "%s"',
                'BBC\\ProgrammesPagesService\\Domain\\Entity\\Version',
                (is_object($item) ? get_class($item) : gettype($item))
            ));
        }
    }
}
