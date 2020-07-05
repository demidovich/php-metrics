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
        $registry = new CollectorRegistry(
            new InMemory()
        );

        $storage = new Storage(
            $registry,
            $this->metrics()
        );

        $this->asserts($storage);
    }

    public function test_inmemory()
    {
        $metrics = $this->metrics();
        $storage = Storage::create('in-memory', [], $metrics);

        $this->asserts($storage);
    }

    public function test_apc()
    {
        $metrics = $this->metrics();
        $storage = Storage::create('apc', [], $metrics);

        $this->asserts($storage);
    }

    public function test_redis()
    {
        $metrics = $this->metrics();
        $host    = isset($_SERVER['REDIS_HOST']) ? $_SERVER['REDIS_HOST'] : '127.0.0.1';
        $storage = Storage::create('redis', ['host' => $host], $metrics);

        $this->asserts($storage);
    }

    public function test_invalid_adapter()
    {
        $this->expectExceptionMessageMatches("/^Invalid CollectorRegistry adapter/i");

        Storage::create('incorrect-adapter', [], $this->metrics());
    }

    private function asserts(Storage $storage)
    {
        $this->assertInstanceOf(Storage::class, $storage);

        $storage->persist();
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

    public function test_debug()
    {
        $logger = $this->createMock(LoggerInterface::class);

        $logger->expects($this->at(0))
            ->method('info')
            ->with($this->stringContains('metrics'));

        $metrics = $this->metrics();
        $storage = Storage::create('in-memory', [], $metrics);
        $storage->debug($logger);

        $storage->persist();
    }
}
