<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use InvalidArgumentException;
use DateTimeImmutable;

class CollapsedBroadcastMapper implements MapperInterface
{
    use Traits\ProgrammeUtilitiesTrait;
    use Traits\SegmentUtilitiesTrait;

    public function getApsObject($broadcast)
    {
        /** @var CollapsedBroadcast $broadcast */
        $this->assertIsCollapsedBroadcast($broadcast);

        return (object) $output = [
            'is_repeat' => $broadcast->isRepeat(),
            'is_blanked' => $broadcast->isBlanked(),
            'schedule_date' => $this->formatDate($broadcast->getStartAt()),
            'start' => $this->formatDateTime($broadcast->getStartAt()),
            'end' => $this->formatDateTime($broadcast->getEndAt()),
            'duration' => $broadcast->getDuration(),
            'service' => $this->getServiceAndOutlets($broadcast->getServices()),
            'programme' => $this->getProgramme($broadcast->getProgrammeItem()),
        ];
    }

    protected function assertIsCollapsedBroadcast($item)
    {
        if (!($item instanceof CollapsedBroadcast)) {
            throw new InvalidArgumentException(sprintf(
                'Entity should be an instance of "%s". Got "%s"',
                'BBC\\ProgrammesPagesService\\Domain\\Entity\\CollapsedBroadcast',
                (is_object($item) ? get_class($item) : gettype($item))
            ));
        }
    }

    private function getProgramme(ProgrammeItem $programme)
    {
        $output = [
            'type' => $this->getProgrammeType($programme),
            'pid' => (string) $programme->getPid(),
            'position' => $programme->getPosition(),
            'title' => $programme->getTitle(),
            'short_synopsis' => $programme->getShortSynopsis(),
            'media_type' => $programme->getMediaType(),
            'duration' => $programme->getDuration(),
            'image' => $this->getImageObject($programme->getImage()),
            'display_titles' => $this->getDisplayTitle($programme),
            'first_broadcast_date' => $this->formatDateTime($programme->getFirstBroadcastDate()),
        ];

        if (!is_null($this->mapSegmentOwnership($programme))) {
            $output['ownership'] = $this->mapSegmentOwnership($programme);
        }

        if ($programme->getParent()) {
            $output['programme'] = $this->getParent($programme->getParent());
        }

        if ($programme->isStreamable()) {
            if ($programme->getStreamableUntil()) {
                $output['available_until'] = $programme->getStreamableUntil() ?
                    $this->formatDateTime($programme->getStreamableUntil()) :
                    null;
            }

            $output['actual_start'] = $programme->getStreamableFrom() ?
                $this->formatDateTime($programme->getStreamableFrom()) :
                null;
        }

        $output['is_available_mediaset_pc_sd'] = $programme->isStreamable();
        $output['is_legacy_media'] = false; // This isn't actually used anywhere

        if ($programme->isStreamable()) {
            $output['media'] = $this->getMedia($programme);
        }

        return (object) $output;
    }

    private function getParent(Programme $programme)
    {
        $output = [
            'type' => $this->getProgrammeType($programme),
            'pid' => (string) $programme->getPid(),
            'title' => $programme->getTitle(),
            'position' => $programme->getPosition(),
            'image' => $this->getImageObject($programme->getImage()),
            'expected_child_count' => ($programme instanceof ProgrammeContainer) ?
                $programme->getExpectedChildCount() :
                null,
            'first_broadcast_date' => $this->getFirstBroadcastDate($programme),
        ];

        if (!is_null($this->mapSegmentOwnership($programme))) {
            $output['ownership'] = $this->mapSegmentOwnership($programme);
        }

        if (!is_null($programme->getParent())) {
            $output['programme'] = $this->getParent($programme->getParent());
        }

        return (object) $output;
    }

    private function getMedia(ProgrammeItem $programme)
    {
        $output = [];
        $output['format'] = $this->getMediaType($programme) === 'audio' ? 'audio' : 'video';

        $action = $this->getMediaType($programme) === 'audio' ? 'listen' : 'watch';

        $output['expires'] = $this->formatDateTime($programme->getStreamableUntil());
        $output['availability'] = 'Available to ' . $action;

        if (!is_null($programme->getStreamableUntil())) {
            $remainingSeconds = $programme->getStreamableUntil()->getTimestamp() -
                (new DateTimeImmutable())->getTimestamp();

            // If over 400 days, ignore time left, according to APS
            // https://repo.dev.bbc.co.uk/services/aps/trunk/lib/Models/Availability.pm
            if ($remainingSeconds <= 86400 * 400) {
                if ($remainingSeconds >= 86400 * 30) {
                    $remainingTime = round($remainingSeconds/(86400.0 * 30.0));
                    $unit = 'month';
                } elseif ($remainingSeconds >= 86400) {
                    $remainingTime = round($remainingSeconds/86400.0);
                    $unit = 'day';
                } elseif ($remainingSeconds >= 3600) {
                    $remainingTime = round($remainingSeconds/3600.0);
                    $unit = 'hour';
                } else {
                    $remainingTime = round($remainingSeconds/60.0);
                    $unit = 'minute';
                }

                if ($remainingTime > 1) {
                    $unit .= 's';
                }

                $output['availability'] = $remainingTime . ' ' . $unit . ' left to ' . $action;
            } // If over 400 days, remove 'expires' field
            else {
                unset($output['expires']);
            }
        }

        return (object) $output;
    }

    private function getServiceAndOutlets(array $service)
    {
        /** @var Network $network */
        $network = $service[0]->getNetwork();

        $output = [
            'type' => $network->getType(),
            'id' => (string) $network->getNid(),
            'key' => (string) $network->getUrlKey(),
            'title' => $network->getName(),
        ];

        if (count($service) >= 2 || (count($service) === 1 && $service[0]->getSid() != $network->getNid())) {
            /** @var Service $s */
            $output['outlets'] = [];
            foreach ($service as $s) {
                $output['outlets'][] = (object) [
                    'id'    => (string) $s->getSid(),
                    'key'   => $s->getUrlKey(),
                    'title' => $s->getShortName(),
                ];
            }
        }

        return (object) $output;
    }
}
