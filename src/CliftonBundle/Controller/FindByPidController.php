<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\FindByPidProgrammeMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
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

    private function programmeResponse(Programme $programme)
    {
        // Related Links
        $relatedLinks = [];
        if ($programme->getRelatedLinksCount()) {
            $rls = $this->get('pps.related_links_service');
            $relatedLinks = $rls->findByRelatedToProgramme($programme);
        }

        // Peers
        $nextSibling = null;
        $previousSibling = null;
        if ($programme->getParent()) {
            /** @var ProgrammesService $ps */
            $ps = $this->get('pps.programmes_service');
            $nextSibling = $ps->findNextSiblingByProgramme($programme);
            $previousSibling = $ps->findPreviousSiblingByProgramme($programme);
        }

        // Versions
        $versions = [];
        if ($programme instanceof ProgrammeItem) {
            $vs = $this->get('pps.versions_service');
            $versions = $vs->findByProgrammeItem($programme);
        }

        $apsProgramme = $this->mapSingleApsObject(
            new FindByPidProgrammeMapper(),
            $programme,
            $relatedLinks,
            $nextSibling,
            $previousSibling,
            $versions
        );

        return $this->json([
            'programme' => $apsProgramme,
        ]);
    }
}
