<?php

namespace BBC\CliftonBundle\Controller;

use BBC\CliftonBundle\ApsMapper\MusicArtistsMapper;
use BBC\CliftonBundle\ApsMapper\MusicChartArtistMapper;
use BBC\CliftonBundle\ApsMapper\MusicChartNetworkMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Contributor;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Service\NetworksService;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class MusicArtistsController extends BaseApsController
{
    const CHART_PERIOD_TEXT = 'Past 7 days';

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

    public function chartAction(Request $request): JsonResponse
    {
        return $this->artistChartFeed();
    }

    public function chartServiceAction(
        Request $request,
        string $networkKey
    ): JsonResponse {

        /** @var NetworksService $networksService */
        $networksService = $this->get('pps.networks_service');
        $network = $networksService->findByUrlKeyWithDefaultService($networkKey);

        if (!$network || !$network->getDefaultService()) {
            throw $this->createNotFoundException('Service not found');
        }

        return $this->artistChartFeed($network);
    }

    private function artistChartFeed(Network $network = null)
    {
        $now = $this->get('clifton.application_time');
        $oneWeekAgo = $now->sub(new \DateInterval('P7D'));

        $service = null;
        if ($network) {
            $service = $network->getDefaultService();
        }

        $artistsResult = $this->get('pps.contributors_service')
            ->findAllMostPlayed($oneWeekAgo, $now, $service);

        $artists = $this->mapManyApsObjects(
            new MusicChartArtistMapper(),
            $artistsResult
        );

        $feed = [
            'start' => $this->formatChartDate($oneWeekAgo),
            'end' => $this->formatChartDate($now),
            'period' => self::CHART_PERIOD_TEXT,
        ];

        if ($network) {
            $serviceOutput = $this->mapSingleApsObject(
                new MusicChartNetworkMapper(),
                $network
            );
            $feed['service'] = $serviceOutput;
        }

        $feed['artists'] = $artists;

        return $this->json(['artists_chart' => $feed]);
    }

    private function formatChartDate(DateTimeImmutable $date)
    {
        return $date->format('Y-m-d');
    }
}
