<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Stub\AppMetrics;
use Metrics\Debug;
use Psr\Log\LoggerInterface;

class DebugTest extends TestCase
{
    private $labels = [
        'route'  => 'myroute',
        'method' => 'get',
        'node'   => '10.0.0.1',
    ];

    private function metrics(): AppMetrics
    {
        $metrics = new AppMetrics(hrtime(true), $this->labels);
        
        $metrics->startPhp();
        usleep(100);
        
        $metrics->startMongo();
        usleep(100);

        $metrics->registerSigninAttempt();

        return $metrics;
    }

    public function test_debug()
    {
        $logger = $this->createMock(LoggerInterface::class);

        $logger
            ->expects($this->exactly(1))
            ->method('info')
            ->with($this->stringContains('metrics'));

        $metrics = $this->metrics();

        $debug = new Debug($logger);
        $debug->toLog($metrics);
    }

    public function test_metric_debug_init()
    {
        $logger  = $this->createMock(LoggerInterface::class);
        $metrics = $this->metrics();

        $this->assertNull($metrics->initDebug($logger));
    }
}
