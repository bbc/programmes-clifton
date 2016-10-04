<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\Entity\VersionType;
use InvalidArgumentException;
use stdClass;

class FindByPidVersionMapper implements MapperInterface
{
    use Traits\ProgrammeUtilitiesTrait;
    use Traits\VersionUtilitiesTrait;

    /**
     * @param Version $version
     * @param array $contributions
     * @param array $segmentEvents
     * @param Broadcast[] $broadcasts
     * @return stdClass
     */
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

        // APS logic. Reverse the broadcasts so we have broadcasts ascending
        $broadcasts = array_reverse($broadcasts);

        $output = [
            'canonical' => $isCanonical,
            'pid' => $version->getPid(),
            'duration' => $version->getDuration(),
            'parent' => $this->getParent($version->getProgrammeItem()),
            'types' => array_map(function (VersionType $vt) {
                return $vt->getName();
            }, $version->getVersionTypes()),
            'contributors' => array_map([$this, 'getContributor'], $contributions),
            'segment_events' => array_map([$this, 'mapVersionSegmentEvent'], $segmentEvents),
            'broadcasts' => array_map([$this, 'getBroadcast'], $broadcasts),
            'availabilities' => [], // Not used anymore
        ];

        return (object) $output;
    }

    private function getParent(ProgrammeItem $programme)
    {
        $output = [
            'type' => $this->getProgrammeType($programme),
            'pid' => (string) $programme->getPid(),
            'title' => $this->getProgrammeTitle($programme->getTitle()),
        ];

        return (object) ['programme' => (object) $output];
    }

    private function getContributor(Contribution $contribution)
    {
        $output = [
            'character_name' => $contribution->getCharacterName() ?? null,
            'name' => $contribution->getContributor()->getName(),
            'family_name' => null,
            'given_name' => null,
            'role' => $contribution->getCreditRole(),
        ];

        $musicBrainzId = $contribution->getContributor()->getMusicBrainzId();
        if ($musicBrainzId) {
            $output['musicbrainz_gid'] = $musicBrainzId;
        }

        return (object) $output;
    }

    private function getBroadcast(Broadcast $broadcast)
    {
        return (object) [
            'is_repeat' => $broadcast->isRepeat(),
            'is_blanked' => $broadcast->isBlanked(),
            'pid' => $broadcast->getPid(),
            'schedule_date' => $this->formatDate($broadcast->getStartAt()),
            'start' => $this->formatDateTime($broadcast->getStartAt()),
            'end' =>  $this->formatDateTime($broadcast->getEndAt()),
            'duration' => $broadcast->getDuration(),
            'service' => $this->getService($broadcast->getService()),
        ];
    }

    private function getService(Service $service)
    {
        return (object) [
            'id' => $service->getSid(),
            'key' => $service->getNetwork()->getUrlKey() ?: "",
            'title' => $service->getShortName(),
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
