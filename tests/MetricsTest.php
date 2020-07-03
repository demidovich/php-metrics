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
        $timers = $metric->timersInMilliseconds();

        $this->assertArrayHasKey('php_init', $timers);
        $this->assertEquals(1, round($timers['php_init'], 0));
    }

    public function test_runtime_php()
    {
        $metric = $this->metrics();
        $metric->startPhp();
        usleep(1000);

        $timers = $metric->timersInMilliseconds();

        $this->assertArrayHasKey('php', $timers);
        $this->assertEquals(1, round($timers['php'], 0));
    }

    public function test_runtime_custom_timer()
    {
        $metric = $this->metrics();
        $metric->startMongo();
        usleep(1000);

        $timers = $metric->timersInMilliseconds();

        $this->assertArrayHasKey('mongo', $timers);
        $this->assertEquals(1, round($timers['mongo'], 0));
    }

    public function test_runtime_total()
    {
        $metric = $this->metrics(1000);
        $metric->startPhp();
        usleep(1000);
        $metric->startMongo();
        usleep(1000);

        $timers = $metric->timersInMilliseconds();

        $this->assertArrayHasKey('total', $timers);
        $this->assertEquals(3, round($timers['total'], 0));
    }

    public function test_runtime_spent()
    {
        $metric = $this->metrics();
        $metric->startPhp();
        usleep(5000);

        $metric->spentMongo(1000);

        $timers = $metric->timersInMilliseconds();

        $this->assertArrayHasKey('mongo', $timers);
        $this->assertEquals(4, round($timers['php'], 0));
        $this->assertEquals(1, round($timers['mongo'], 0));
    }
}