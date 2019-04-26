<?php

namespace BBC\CliftonBundle\Controller;

use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AvailabilityController extends BaseApsController
{
    public function findByPidAction(Request $request, string $pid): JsonResponse
    {
        $pid = new Pid($pid);

        /** @var ProgrammesService $service */
        $service = $this->get('pps.programmes_service');
        $programme = $service->findByPidFull($pid);
        if ($programme) {
            return $this->programmeResponse($programme);
        }



        throw $this->createNotFoundException(sprintf('The item with PID "%s" was not found', $pid));
    }

    private function programmeResponse(Programme $programme)
    {
        $res = [
            'pid' => $programme->getPid(),
            'isStreamable' => $programme->isStreamable(),
            'isStreamableAlternate' => $programme->isStreamableAlternate(),
        ];
        if ($programme instanceof ProgrammeContainer) {
            /** ProgrammeContainer $programme */
            $res['availableClipCount'] = $programme->getAvailableClipsCount();
            $res['availableEpisodesCount'] = $programme->getAvailableEpisodesCount();
        }
        if ($programme instanceof ProgrammeItem) {
            $res['streamableFrom'] = $this->format($programme->getStreamableFrom());
            $res['streamableUntil'] = $this->format($programme->getStreamableUntil());
        }
        if ($programme instanceof Episode) {
            /** Episode $programme */
            $res['availableClipCount'] = $programme->getAvailableClipsCount();
            $res['aggregatedGalleriesCount'] = $programme->getAggregatedGalleriesCount();
        }
        return $this->json($res);
    }

    private function format($date)
    {
        return $date instanceof \DateTimeInterface ? $date->format(DATE_ISO8601) : null;
    }
}
