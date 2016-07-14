<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\FindByPidProgrammeMapper;
use BBC\CliftonBundle\ApsMapper\FindByPidVersionMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
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

        // Attempt to find a Version
        $version = $this->get('pps.versions_service')->findByPidFull($pid);
        if ($version) {
            return $this->versionResponse($version);
        }

        // TODO
        // Attempt to find a Segment

        // TODO
        // Attempt to find a SegmentEvent

        throw $this->createNotFoundException(sprintf('The item with PID "%s" was not found', $pid));
    }

    private function programmeResponse(Programme $programme): JsonResponse
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

    private function versionResponse(Version $version): JsonResponse
    {
        // Contributors
        $cs = $this->get('pps.contributions_service');
        $contributions = $cs->findByContributionToVersion($version);
        if (!$contributions) {
            // If no contributions on Version, try on the Programme
            $contributions = $cs->findByContributionToProgramme(
                $version->getProgrammeItem()
            );
        }

        // Segment Events
        $segmentEvents = [];
        // TODO

        // Broadcasts
        $broadcasts = [];
        // TODO

        $apsVersion = $this->mapSingleApsObject(
            new FindByPidVersionMapper(),
            $version,
            $contributions,
            $segmentEvents,
            $broadcasts
        );

        return $this->json([
            'version' => $apsVersion,
        ]);
    }
}
