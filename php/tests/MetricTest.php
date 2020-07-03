<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\AppMetric;

class MetricTest extends TestCase
{
    private function metric(int $initMicroseconds = null): AppMetric
    {
        $startTime = hrtime(true);

        if ($initMicroseconds) {
            usleep($initMicroseconds);
        }

        return new AppMetric($startTime);
    }

    public function test_metric_namespace()
    {
        $metric = $this->metric();

        $this->assertEquals('myapp', $metric->namespace());
    }

    public function test_metric_runtime_php_init()
    {
        $metric = $this->metric(1000);
        $timers = $metric->timersInMilliseconds();

        $this->assertArrayHasKey('php_init', $timers);
        $this->assertEquals(1, round($timers['php_init'], 0));
    }

    public function test_metric_runtime_php()
    {
        $metric = $this->metric();
        $metric->startPhp();
        usleep(1000);

        $timers = $metric->timersInMilliseconds();

        $this->assertArrayHasKey('php', $timers);
        $this->assertEquals(1, round($timers['php'], 0));
    }

    public function test_metric_runtime_custom_timer()
    {
        $metric = $this->metric();
        $metric->startMongo();
        usleep(1000);

        $timers = $metric->timersInMilliseconds();

        $this->assertArrayHasKey('mongo', $timers);
        $this->assertEquals(1, round($timers['mongo'], 0));
    }

    public function test_metric_runtime_total()
    {
        $metric = $this->metric(1000);
        $metric->startPhp();
        usleep(1000);
        $metric->startMongo();
        usleep(1000);

        $timers = $metric->timersInMilliseconds();

        $this->assertArrayHasKey('total', $timers);
        $this->assertEquals(3, round($timers['total'], 0));
    }
}