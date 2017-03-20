<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\VersionSegmentEventsMapper;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VersionSegmentEventsController extends BaseApsController
{
    public function segmentEventsAction(Request $request, string $pid): Response
    {
        $pid = new Pid($pid);

        // Attempt to find a version
        $version = $this->get('pps.versions_service')->findByPidFull($pid);
        if ($version) {
            return $this->versionResponse($version);
        }

        // Attempt to find a clip or episode
        $programmeItem = $this->get('pps.programmes_service')->findByPid($pid, 'ProgrammeItem');
        if ($programmeItem) {
            return $this->episodeOrClipResponse($programmeItem);
        }

        throw $this->createNotFoundException('Episode, Clip or Version not found');
    }

    private function versionResponse(Version $version)
    {
        if (!$version->getSegmentEventCount()) {
            throw $this->createNotFoundException('No segments');
        }

        // Segment events
        $segmentEventsService = $this->get('pps.segment_events_service');
        $segmentEvents = $segmentEventsService->findByVersionWithContributions(
            $version,
            $segmentEventsService::NO_LIMIT
        );

        $apsSegmentEvents = $this->mapManyApsObjects(
            new VersionSegmentEventsMapper(),
            $segmentEvents
        );

        return $this->json(['segment_events' => $apsSegmentEvents]);
    }

    private function episodeOrClipResponse(ProgrammeItem $programmeItem)
    {
        $vs = $this->get('pps.versions_service');
        /** @var Version $originalVersion */
        $originalVersion = $vs->findOriginalVersionForProgrammeItem($programmeItem);

        if ($originalVersion && $originalVersion->getSegmentEventCount()) {
            // Redirect to original version's segments feed
            return $this->redirectToRoute('aps.version_segment_events', ['pid' => $originalVersion->getPid()]);
        }

        // 404 if original version doesn't have segments
        throw $this->createNotFoundException('The canonical version of that episode does not have segments');
    }
}
