<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\MusicArtistsMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Contributor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class MusicArtistsController extends BaseApsController
{
    public function showAction(
        Request $request,
        string $musicBrainzId
    ): JsonResponse {

        $contributorService = $this->get('pps.contributors_service');
        $segmentEventsService = $this->get('pps.segment_events_service');

        /** @var Contributor $contributor */
        $contributor = $contributorService->findByMusicBrainzId($musicBrainzId);

        if (is_null($contributor)) {
            throw $this->createNotFoundException('Artist not found');
        }

        $segmentEventsResult = $segmentEventsService->findLatestBroadcastedForContributor(
            $contributor,
            50
        );


        $artist = $this->mapSingleApsObject(
            new MusicArtistsMapper(),
            $contributor,
            $segmentEventsResult
        );

        return $this->json([
            'artist' => $artist,
        ]);
    }
}
