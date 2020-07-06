<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Metrics\Storage;
use Tests\Stub\AppMetrics;

class MetricsTest extends TestCase
{
    private function metrics(int $initMicroseconds = null, array $labels = []): AppMetrics
    {
        $startTime = hrtime(true);

        if ($initMicroseconds) {
            usleep($initMicroseconds);
        }

        return new AppMetrics($startTime, $labels);
    }

    public function test_init_storage()
    {
        $metrics = $this->metrics();
        $storage = Storage::create('in-memory');
        $metrics->initStorage($storage);

        $this->assertInstanceOf(Storage::class, $metrics->storage());
    }

    public function test_namespace()
    {
        $metrics = $this->metrics();

        $this->assertEquals('myapp', $metrics->namespace());
    }

    public function test_labels()
    {
        $initLabels = [
            'route'  => 'my.route',
            'method' => 'get',
        ];

        $metrics = $this->metrics(null, $initLabels);
        $labels  = $metrics->labels()->all();

        $this->assertArrayHasKey('route', $labels);
        $this->assertArrayHasKey('method', $labels);

        $this->assertEquals($initLabels['route'], $labels['route']);
        $this->assertEquals($initLabels['method'], $labels['method']);
    }

    public function test_memory_usage()
    {
        $metrics = $this->metrics();

        $expected = \memory_get_usage(false);

        $this->assertEqualsWithDelta($expected, $metrics->memoryUsage(), 500);
    }

    public function test_runtime_php_init()
    {
        $metrics = $this->metrics(1000);
        $timers = $metrics->runtime()->allInMilliseconds(0);

        $this->assertArrayHasKey('php_init', $timers);
        $this->assertEquals(1, $timers['php_init']);
    }

    public function test_runtime_php()
    {
        $metrics = $this->metrics();

        $metrics->startPhp();
        usleep(1000);

        $timers = $metrics->runtime()->allInMilliseconds(0);

        $this->assertArrayHasKey('php', $timers);
        $this->assertEquals(1, $timers['php']);
    }

    public function test_runtime_custom_timer()
    {
        $metrics = $this->metrics();

        $metrics->startMongo();
        usleep(1000);

        $timers = $metrics->runtime()->allInMilliseconds(0);

        $this->assertArrayHasKey('mongo', $timers);
        $this->assertEquals(1, $timers['mongo']);
    }

    public function test_runtime_retry()
    {
        $metrics = $this->metrics(1000);

        $metrics->startPhp();
        usleep(1000);

        $timer = $metrics->runtime()->timer();
        $startedAt = $metrics->runtime()->timerStartedAt();

        $metrics->startPhp();
        usleep(1000);

        $this->assertEquals($timer,     $metrics->runtime()->timer());
        $this->assertEquals($startedAt, $metrics->runtime()->timerStartedAt());
    }

    public function test_runtime_spent()
    {
        $metrics = $this->metrics();
        $metrics->startPhp();
        usleep(50000);

        $metrics->spentMongo(10000);

        $timers = $metrics->runtime()->allInMilliseconds();

        $this->assertArrayHasKey('mongo', $timers);
        $this->assertEquals(10, $timers['mongo']);

        $this->assertEqualsWithDelta(40, $timers['php'], 2);
    }

    public function test_runtime_init_spent()
    {
        $metrics = $this->metrics();
        usleep(50000);

        $metrics->spentMongo(10000);

        $timers = $metrics->runtime()->allInMilliseconds(0);

        $this->assertArrayHasKey('mongo', $timers);
        $this->assertEquals(10, $timers['mongo']);

        $this->assertEqualsWithDelta(40, $timers['php_init'], 2);
        $this->assertArrayNotHasKey('php', $timers);
    }

    public function test_event_counter()
    {
        $metrics = $this->metrics();
        $metrics->registerSigninAttempt();

        $couners = $metrics->counters()->all();

        $this->assertArrayHasKey('signin_attempt', $couners);
        $this->assertEquals(1, $couners['signin_attempt']);
    }

    public function test_event_counter_increase5()
    {
        $metrics = $this->metrics();
        $metrics->registerSigninAttempt(5);

        $couners = $metrics->counters()->all();

        $this->assertArrayHasKey('signin_attempt', $couners);
        $this->assertEquals(5, $couners['signin_attempt']);
    }
}