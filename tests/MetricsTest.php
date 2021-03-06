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

        $metrics = new AppMetrics($startTime, $labels);
        $metrics->setHttpMethod('get');
        $metrics->setHttpRoute('api.resourse@read');
        $metrics->setHttpStatus(200);

        return $metrics;
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

    public function test_construct_labels()
    {
        $initLabels = [
            'node' => '10.0.0.1',
        ];

        $metrics = $this->metrics(null, $initLabels);
        $labels  = $metrics->labels()->all();

        $this->assertArrayHasKey('node', $labels);
        $this->assertEquals($initLabels['node'], $labels['node']);
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
        $metrics->runtime()->stop();

        $timers = $metrics->runtime()->allInMilliseconds(0);

        $this->assertArrayHasKey('php_init', $timers);
        $this->assertGreaterThanOrEqual(1, $timers['php_init']);
        $this->assertLessThanOrEqual(3, $timers['php_init']);
    }

    public function test_runtime_php()
    {
        $metrics = $this->metrics();
        $metrics->startPhp();
        usleep(1000);
        $metrics->runtime()->stop();

        $timers = $metrics->runtime()->allInMilliseconds(0);

        $this->assertArrayHasKey('php', $timers);
        $this->assertGreaterThanOrEqual(1, $timers['php']);
        $this->assertLessThanOrEqual(3, $timers['php']);
    }

    public function test_runtime_custom_timer()
    {
        $metrics = $this->metrics();
        $metrics->startMongo();
        usleep(1000);
        $metrics->runtime()->stop();

        $timers = $metrics->runtime()->allInMilliseconds(0);

        $this->assertArrayHasKey('mongo', $timers);
        $this->assertGreaterThanOrEqual(1, $timers['mongo']);
        $this->assertLessThanOrEqual(3, $timers['mongo']);
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
        $metrics->runtime()->stop();

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
        $metrics->runtime()->stop();

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

    public function test_http_method()
    {
        $metrics = $this->metrics();
        $this->assertEquals('get', $metrics->httpMethod());

        $metrics->setHttpMethod('post');
        $this->assertEquals('post', $metrics->httpMethod());
    }

    public function test_http_route()
    {
        $metrics = $this->metrics();
        $this->assertEquals('api.resourse@read', $metrics->httpRoute());

        $metrics->setHttpRoute('api.resourse@update');
        $this->assertEquals('api.resourse@update', $metrics->httpRoute());
    }

    public function test_http_status()
    {
        $metrics = $this->metrics();
        $this->assertEquals(200, $metrics->httpStatus());

        $metrics->setHttpStatus(500);
        $this->assertEquals(500, $metrics->httpStatus());
    }
}