<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\FindByPidProgrammeMapper;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class FindByPidController extends BaseApsController
{
    public function findByPidAction(Request $request, string $pid): JsonResponse
    {
        $pid = new Pid($pid);

        // Attempt to find a Programme
        $programme = $this->get('pps.programmes_service')->findByPidFull($pid);
        if ($programme) {
            return $this->programmeResponse($programme);
        }

        // TODO
        // Attempt to find a Version

        // TODO
        // Attempt to find a Segment

        // TODO
        // Attempt to find a SegmentEvent

        throw $this->createNotFoundException(sprintf('The item with PID "%s" was not found', $pid));
    }

    private function programmeResponse($programme)
    {
        // $descendantsResult = $this->programmesService->findDescendantsByPid($programme->getPid());
        $relatedLinks = [];
        $peers = [];
        $versions = [];

        $apsProgramme = $this->mapSingleApsObject(
            new FindByPidProgrammeMapper(),
            $programme,
            $relatedLinks,
            $peers,
            $versions
        );

        return $this->json([
            'programme' => $apsProgramme,
        ]);
    }
}
