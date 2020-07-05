<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Stub\AppMetrics;
use Metrics\Storage;

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

    public function storages()
    {
        $metrics = $this->metrics();

        $redisHost = isset($_SERVER['REDIS_HOST']) ? $_SERVER['REDIS_HOST'] : '127.0.0.1';

        return [
            'inmemory' => [Storage::create('in-memory', [], $metrics)],
            'apc'      => [Storage::create('apc', [], $metrics)],
            'redis'    => [Storage::create('redis', ['host' => $redisHost], $metrics)],
        ];
    }

    /**
     * @dataProvider storages
     */
    public function test_inmemory(Storage $storage)
    {
        $storage->persist();
        $persisted = $storage->fetch();

        $this->assertInstanceOf(Storage::class, $storage);

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