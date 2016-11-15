<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\FindByPidProgrammeMapper;
use BBC\CliftonBundle\ApsMapper\FindByPidVersionMapper;
use BBC\CliftonBundle\ApsMapper\FindByPidSegmentMapper;
use BBC\CliftonBundle\ApsMapper\FindByPidSegmentEventMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
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

        // Attempt to find a Segment
        $segment = $this->get('pps.segments_service')->findByPidFull($pid);
        if ($segment) {
            return $this->segmentResponse($segment);
        }

        // Attempt to find a SegmentEvent
        $segmentEvent = $this->get('pps.segment_events_service')->findByPidFull($pid);
        if ($segmentEvent) {
            return $this->segmentEventResponse($segmentEvent);
        }

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
        $contributions = [];
        $contributionsService = $this->get('pps.contributions_service');

        if ($version->getContributionsCount()) {
            $contributions = $contributionsService->findByContributionToVersion($version);
        } elseif ($version->getProgrammeItem()->getContributionsCount()) {
            // If no contributions on Version, try on the Programme
            $contributions = $contributionsService->findByContributionToProgramme(
                $version->getProgrammeItem()
            );
        }

        // Segment Events with the contributions
        $segmentEvents = [];
        if ($version->getSegmentEventCount()) {
            $ses = $this->get('pps.segmentevents_service');
            $segmentEvents = $ses->findByVersionWithContributions($version);
        }

        // Broadcasts
        $broadcastsService = $this->get('pps.broadcasts_service');
        $broadcasts = $broadcastsService->findByVersion($version, 100);

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

    private function segmentResponse(Segment $segment): JsonResponse
    {
        $segmentEventsService = $this->get('pps.segment_events_service');

        $segmentEvents = $segmentEventsService->findBySegmentFull($segment, true, $segmentEventsService::NO_LIMIT);

        $apsSegment = $this->mapSingleApsObject(
            new FindByPidSegmentMapper(),
            $segment,
            $segmentEvents,
            true
        );

        return $this->json([
            'segment' => $apsSegment,
        ]);
    }

    private function segmentEventResponse(SegmentEvent $segmentEvent): JsonResponse
    {
        $segmentEventsService = $this->get('pps.segment_events_service');

        $segmentEventsBySegment = $segmentEventsService->findBySegmentFull(
            $segmentEvent->getSegment(),
            true,
            $segmentEventsService::NO_LIMIT
        );

        $apsSegmentEvent = $this->mapSingleApsObject(
            new FindByPidSegmentEventMapper(),
            $segmentEvent,
            $segmentEventsBySegment
        );

        return $this->json([
            'segment_event' => $apsSegmentEvent,
        ]);
    }
}
