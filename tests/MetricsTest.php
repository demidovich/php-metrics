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
            $startTime -= $initMicroseconds * 1e3;
        }

        $metrics = new AppMetrics($startTime, $labels);
        $metrics->setHttpMethod("get");
        $metrics->setHttpRoute("api.resourse@read");
        $metrics->setHttpStatus(200);

        return $metrics;
    }

    public function test_init_storage()
    {
        $metrics = $this->metrics();
        $storage = Storage::create("in-memory");
        $metrics->initStorage($storage);

        $this->assertInstanceOf(Storage::class, $metrics->storage());
    }

    public function test_namespace()
    {
        $metrics = $this->metrics();

        $this->assertEquals("myapp", $metrics->namespace());
    }

    public function test_construct_labels()
    {
        $initLabels = [
            "node" => "10.0.0.1",
        ];

        $metrics = $this->metrics(null, $initLabels);
        $labels  = $metrics->labels()->all();

        $this->assertArrayHasKey("node", $labels);
        $this->assertEquals($initLabels["node"], $labels["node"]);
    }

    public function test_memory_usage()
    {
        $metrics = $this->metrics();

        $expected = \memory_get_peak_usage(true);

        $this->assertEqualsWithDelta($expected, $metrics->memoryUsage(), 500);
    }

    public function test_runtime_php_init()
    {
        $metrics = $this->metrics(1000);
        $timers  = $metrics->runtime()->fetchResultsInSeconds();

        $this->assertArrayHasKey("php_init", $timers);
        $this->assertGreaterThanOrEqual(0.001, $timers["php_init"]);
        $this->assertLessThanOrEqual(0.002, $timers["php_init"]);
    }

    public function test_runtime_php()
    {
        $metrics = $this->metrics(1000);
        $metrics->startPhp();
        usleep(1000);

        $timers = $metrics->runtime()->fetchResultsInSeconds();

        $this->assertArrayHasKey("php", $timers);
        $this->assertGreaterThanOrEqual(0.001, $timers["php"]);
        $this->assertLessThanOrEqual(0.002, $timers["php"]);
    }

    public function test_runtime_custom_timer()
    {
        $metrics = $this->metrics(1000);
        $metrics->startMongo();
        usleep(1000);

        $timers = $metrics->runtime()->fetchResultsInSeconds();

        $this->assertArrayHasKey("mongo", $timers);
        $this->assertGreaterThanOrEqual(0.001, $timers["mongo"]);
        $this->assertLessThanOrEqual(0.002, $timers["mongo"]);
    }

    public function test_runtime_retry()
    {
        $metrics = $this->metrics(1000);
        $metrics->startPhp();
        usleep(1000);

        $metrics->startPhp();
        usleep(1000);

        $timers = $metrics->runtime()->fetchResultsInSeconds();

        $this->assertArrayHasKey("php", $timers);
        $this->assertGreaterThanOrEqual(0.002, $timers["php"]);
        $this->assertLessThanOrEqual(0.003, $timers["php"]);
    }

    public function test_runtime_spent()
    {
        $metrics = $this->metrics(1000);
        $metrics->startPhp();
        usleep(2000);

        $metrics->spentMongo(1000);

        $timers = $metrics->runtime()->fetchResultsInSeconds();

        $this->assertArrayHasKey("php", $timers);
        $this->assertGreaterThanOrEqual(0.001, $timers["php"]);
        $this->assertLessThanOrEqual(0.002, $timers["php"]);

        $this->assertArrayHasKey("mongo", $timers);
        $this->assertGreaterThanOrEqual(0.001, $timers["mongo"]);
        $this->assertLessThanOrEqual(0.002, $timers["mongo"]);
    }

    public function test_runtime_init_spent()
    {
        $metrics = $this->metrics();
        usleep(2000);
        $metrics->spentMongo(1000);

        $timers = $metrics->runtime()->fetchResultsInSeconds();

        $this->assertArrayHasKey("php_init", $timers);
        $this->assertGreaterThanOrEqual(0.001, $timers["php_init"]);
        $this->assertLessThanOrEqual(0.002, $timers["php_init"]);

        $this->assertArrayHasKey("mongo", $timers);
        $this->assertGreaterThanOrEqual(0.001, $timers["mongo"]);
        $this->assertLessThanOrEqual(0.002, $timers["mongo"]);

        $this->assertArrayNotHasKey("php", $timers);
    }

    public function test_event_counter()
    {
        $metrics = $this->metrics();
        $metrics->registerSigninAttempt();

        $couners = $metrics->counters()->all();

        $this->assertArrayHasKey("signin_attempt", $couners);
        $this->assertEquals(1, $couners["signin_attempt"]);
    }

    public function test_event_counter_increase5()
    {
        $metrics = $this->metrics();
        $metrics->registerSigninAttempt(5);

        $couners = $metrics->counters()->all();

        $this->assertArrayHasKey("signin_attempt", $couners);
        $this->assertEquals(5, $couners["signin_attempt"]);
    }

    public function test_http_method()
    {
        $metrics = $this->metrics();
        $this->assertEquals("get", $metrics->httpMethod());

        $metrics->setHttpMethod("post");
        $this->assertEquals("post", $metrics->httpMethod());
    }

    public function test_http_route()
    {
        $metrics = $this->metrics();
        $this->assertEquals("api.resourse@read", $metrics->httpRoute());

        $metrics->setHttpRoute("api.resourse@update");
        $this->assertEquals("api.resourse@update", $metrics->httpRoute());
    }

    public function test_http_status()
    {
        $metrics = $this->metrics();
        $this->assertEquals(200, $metrics->httpStatus());

        $metrics->setHttpStatus(500);
        $this->assertEquals(500, $metrics->httpStatus());
    }
}