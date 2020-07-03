<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Stub\AppMetrics;

class MetricsTest extends TestCase
{
    private function metrics(int $initMicroseconds = null): AppMetrics
    {
        $startTime = hrtime(true);

        if ($initMicroseconds) {
            usleep($initMicroseconds);
        }

        return new AppMetrics($startTime);
    }

    public function test_namespace()
    {
        $metric = $this->metrics();

        $this->assertEquals('myapp', $metric->namespace());
    }

    public function test_runtime_php_init()
    {
        $metric = $this->metrics(1000);
        $timers = $metric->timersInMilliseconds(0);

        $this->assertArrayHasKey('php_init', $timers);
        $this->assertEquals(1, $timers['php_init']);
    }

    public function test_runtime_php()
    {
        $metric = $this->metrics();
        $metric->startPhp();
        usleep(1000);

        $timers = $metric->timersInMilliseconds(0);

        $this->assertArrayHasKey('php', $timers);
        $this->assertEquals(1, $timers['php']);
    }

    public function test_runtime_custom_timer()
    {
        $metric = $this->metrics();
        $metric->startMongo();
        usleep(1000);

        $timers = $metric->timersInMilliseconds(0);

        $this->assertArrayHasKey('mongo', $timers);
        $this->assertEquals(1, $timers['mongo']);
    }

    public function test_runtime_total()
    {
        $metric = $this->metrics(1000);
        $metric->startPhp();
        usleep(1000);
        $metric->startMongo();
        usleep(1000);

        $timers = $metric->timersInMilliseconds(0);

        $this->assertArrayHasKey('total', $timers);
        $this->assertEquals(3, $timers['total']);
    }

    public function test_runtime_spent()
    {
        $metric = $this->metrics();
        $metric->startPhp();
        usleep(5000);

        $metric->spentMongo(1000);

        $timers = $metric->timersInMilliseconds(0);

        $this->assertArrayHasKey('mongo', $timers);
        $this->assertEquals(4, $timers['php']);
        $this->assertEquals(1, $timers['mongo']);
    }
}