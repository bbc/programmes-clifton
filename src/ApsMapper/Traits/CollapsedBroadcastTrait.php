<?php

namespace BBC\CliftonBundle\ApsMapper\Traits;

use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use InvalidArgumentException;
use stdClass;

trait CollapsedBroadcastTrait
{
    use EpisodeItemTrait;

    public function mapCollapsedBroadcast($broadcast): array
    {
        /** @var CollapsedBroadcast $broadcast */
        $this->assertIsCollapsedBroadcast($broadcast);

        // A collapsed broadcast could have more than one network, so we have to split
        $servicesByNetwork = $this->splitServicesByNetwork($broadcast->getServices());

        $output = [];
        foreach ($servicesByNetwork as $serviceByNetwork) {
            $output[] = (object) [
                'is_repeat' => $broadcast->isRepeat(),
                'is_blanked' => $broadcast->isBlanked(),
                'schedule_date' => $this->formatDate($broadcast->getStartAt()),
                'start' => $this->formatDateTime($broadcast->getStartAt()),
                'end' => $this->formatDateTime($broadcast->getEndAt()),
                'duration' => $broadcast->getDuration(),
                'service' => $this->getServiceAndOutlets($serviceByNetwork),
                'programme' => $this->mapEpisodeItem($broadcast->getProgrammeItem()),
            ];
        }

        // Replicate APS ordering of CollapsedBroadcasts by Nid (we can't do that in Pages Service)
        usort($output, function ($a, $b) {
            return strcmp($a->{'service'}->{'id'}, $b->{'service'}->{'id'});
        });

        return $output;
    }

    protected function assertIsCollapsedBroadcast($item): void
    {
        if (!($item instanceof CollapsedBroadcast)) {
            throw new InvalidArgumentException(sprintf(
                'Entity should be an instance of "%s". Got "%s"',
                'BBC\\ProgrammesPagesService\\Domain\\Entity\\CollapsedBroadcast',
                (is_object($item) ? get_class($item) : gettype($item))
            ));
        }
    }

    private function getServiceAndOutlets(array $services): stdClass
    {
        $services = $this->filterBlacklistedServices($services);

        /** @var Network $network */
        $network = $services[0]->getNetwork();

        $output = [
            'type' => $network->getMedium(),
            'id' => (string) $network->getNid(),
            'key' => (string) $network->getUrlKey(),
            'title' => $network->getName(),
        ];

        if (count($services) >= 2 || (count($services) === 1 && (string) $services[0]->getSid() !== (string) $network->getNid())) {
            /** @var Service $s */
            $output['outlets'] = [];
            usort($services, function ($a, $b) {
                return $a->getSid() <=> $b->getSid();
            });
            foreach ($services as $s) {
                $output['outlets'][] = (object) [
                    'id'    => (string) $s->getSid(),
                    'key'   => $s->getUrlKey(),
                    'title' => $s->getShortName(),
                ];
            }
        }

        return (object) $output;
    }

    private function splitServicesByNetwork(array $services): array
    {
        $networks = [];
        foreach ($services as $service) {
            $nid = (string) $service->getNetwork()->getNid();

            if (!array_key_exists($nid, $networks)) {
                $networks[$nid] = [];
            }

            $networks[$nid][] = $service;
        }

        return array_values($networks);
    }

    private function filterBlacklistedServices(array $services): array
    {
        // APS never ingested anything relating to these services as it could
        // not handle the creation of these services without drastic
        // rearchitecting because it couldn't handle the concept of a service
        // that had the same name as one of its outlets, e.g. bbc_three and
        // bbc_three_hd are both outlets of BBC Three.
        //
        // Eventually we should remove this filter, but for now we want to try
        // and make the feeds as similar to APS as is reasonable to avoid noise
        // in the diffs.
        //
        // There should never be a case of a broadcast that is only on one of
        // these services and not on their non-HD counterpart, but just in case
        // there is we should let that through as a having a single broadcast.
        // This better than making making the page wobble

        if (count($services) == 1) {
            return $services;
        }

        $blacklistedServices = [
            'bbc_three_hd',
            'bbc_four_hd',
            'cbbc_hd',
            'cbeebies_hd',
            'bbc_news_channel_hd',
        ];

        return array_values(array_filter(
            $services,
            function ($service) use ($blacklistedServices) {
                return !in_array((string) $service->getSid(), $blacklistedServices);
            }
        ));
    }
}
