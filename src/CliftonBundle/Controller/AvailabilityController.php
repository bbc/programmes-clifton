<?php

namespace BBC\CliftonBundle\Controller;

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
        ];
        if ($programme instanceof ProgrammeContainer) {
            /** ProgrammeContainer $programme */
            $res['availableClipCount'] = $programme->getAvailableClipsCount();
            $res['availableEpisodesCount'] = $programme->getAvailableEpisodesCount();
            $res['isPodcastable'] = $programme->isPodcastable();
        }
        if ($programme instanceof ProgrammeItem) {
            /** ProgrammeItem $programme */
            $res['availableClipCount'] = $programme->getAvailableClipsCount();
            $res['streamableFrom'] = $programme->getStreamableFrom()->format(DATE_ISO8601);
            $res['streamableUntil'] = $programme->getStreamableUntil()->format(DATE_ISO8601);
        }
        return $this->json($res);
    }
}
