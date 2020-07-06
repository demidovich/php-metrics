<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Stub\AppMetrics;
use Metrics\Storage;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use Psr\Log\LoggerInterface;

class StorageTest extends TestCase
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

        $this->assertStringContainsString('route="myroute"', $persisted);
        $this->assertStringContainsString('method="get"',    $persisted);
        $this->assertStringContainsString('node="10.0.0.1"', $persisted);

        $this->assertStringContainsString('myapp_http_memory_usage_total',  $persisted);
        $this->assertStringContainsString('myapp_http_requests_total',      $persisted);
        $this->assertStringContainsString('myapp_http_runtime_mongo',       $persisted);
        $this->assertStringContainsString('myapp_http_runtime_php',         $persisted);
        $this->assertStringContainsString('myapp_http_runtime_php_init',    $persisted);
        $this->assertStringContainsString('myapp_http_runtime_total',       $persisted);
        $this->assertStringContainsString('myapp_signin_attempt_total',     $persisted);
    }
}
