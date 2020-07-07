<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Stub\AppMetrics;
use Metrics\Storage;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;

class StorageTest extends TestCase
{
    private function metrics(): AppMetrics
    {
        $metrics = new AppMetrics(hrtime(true), [
            'node' => '10.0.0.1',
        ]);

        $metrics->setHttpMethod('get');
        $metrics->setHttpRoute('api.books@read');
        $metrics->setHttpStatus(200);

        $metrics->startPhp();
        usleep(100);
        
        $metrics->startMongo();
        usleep(100);

        $metrics->registerSigninAttempt();

        return $metrics;
    }

    public function test_constructor()
    {
        $storage = new Storage(
            new CollectorRegistry(
                new InMemory()
            )        
        );

        $metrics = $this->metrics();
        $metrics->initStorage($storage);

        $this->asserts($metrics, $storage);
    }

    public function test_inmemory()
    {
        $storage = Storage::create('in-memory');

        $metrics = $this->metrics();
        $metrics->initStorage($storage);

        $this->asserts($metrics, $storage);
    }

    public function test_apc()
    {
        $storage = Storage::create('apc');

        $metrics = $this->metrics();
        $metrics->initStorage($storage);

        $this->asserts($metrics, $storage);
    }

    public function test_redis()
    {
        $host    = isset($_SERVER['REDIS_HOST']) ? $_SERVER['REDIS_HOST'] : '127.0.0.1';
        $storage = Storage::create('redis', ['host' => $host]);

        $metrics = $this->metrics();
        $metrics->initStorage($storage);

        $this->asserts($metrics, $storage);
    }

    public function test_invalid_adapter()
    {
        $this->expectExceptionMessageMatches("/^Invalid CollectorRegistry adapter/i");

        Storage::create('incorrect-adapter', []);
    }

    private function asserts(AppMetrics $metrics, Storage $storage)
    {
        $storage->persist($metrics);

        $persisted = $storage->fetch();

        $this->assertStringContainsString('route="api.books@read"', $persisted);
        $this->assertStringContainsString('method="get"', $persisted);
        $this->assertStringContainsString('node="10.0.0.1"', $persisted);

        $this->assertStringContainsString('myapp_http_memory_usage_bytes',    $persisted);
        $this->assertStringContainsString('myapp_http_requests_total',        $persisted);
        $this->assertStringContainsString('myapp_http_runtime_seconds',       $persisted);
        $this->assertStringContainsString('myapp_http_runtime_seconds_total', $persisted);
        $this->assertStringContainsString('myapp_http_statuses_total',        $persisted);
        $this->assertStringContainsString('myapp_signin_attempt_total',       $persisted);
    }
}
