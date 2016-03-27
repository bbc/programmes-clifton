<?php

namespace Tests\BBC\CliftonBundle\Controller;

use Tests\BBC\CliftonBundle\BaseWebTestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

class MonitoringRequestLoggingTest extends BaseWebTestCase
{
    public function testCallsAreLogged()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/status');
        $this->assertResponseStatusCode($client, 200);

        $cwClient = $client->getContainer()->get('clifton.cloudwatch_client');

        $expectedMetrics = [
            $this->expectedMetric('RequestCount', 1, 'Count'),
            $this->expectedMetric('RequestTime', 100, 'Milliseconds'),
        ];

        $allMetrics = $this->getAllMetrics($cwClient);
        // Assert the time value is set
        $this->assertInternalType('int', $allMetrics[1]['MetricData'][0]['Value']);

        // Reset the time value as the actual value on every run
        $allMetrics[1]['MetricData'][0]['Value'] = 100;
        $this->assertEquals($expectedMetrics, $allMetrics);
    }

    public function testCallsFromElbHealthCheckAreNotLogged()
    {
        $client = static::createClient([], [
            'HTTP_USER_AGENT' => 'ELB-HealthChecker/1.0',
        ]);
        $crawler = $client->request('GET', '/status');
        $this->assertResponseStatusCode($client, 200);

        $cwClient = $client->getContainer()->get('clifton.cloudwatch_client');

        $this->assertEquals(0, $cwClient->getMetricCount());
    }

    private function getAllMetrics($mockClient)
    {
        $metrics = [];
        while ($mockClient->getMetricCount()) {
            array_unshift($metrics, $mockClient->getLatestMetric());
        }

        return \GuzzleHttp\Promise\unwrap($metrics);
    }

    private function expectedMetric($name, $value, $unit)
    {
        return [
            'Namespace' => 'BBCApp/clifton',
            'MetricData' => [
                [
                    'MetricName' => $name,
                    'Dimensions' => [
                        [ 'Name' => 'BBCEnvironment', 'Value' => 'sandbox' ],
                    ],
                    'Value' => $value,
                    'Unit' => $unit,
                ],
            ],
        ];
    }
}
