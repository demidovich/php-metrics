<?php

namespace Metric;

use Prometheus\CollectorRegistry;
use Prometheus\Storage\APC;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\Redis;

class MetricCollectorRegistry
{
    private $adapter;
    private $redisConfig;

    public function __construc(string $adapter, array $redisConfig = [])
    {
        $this->adapter = $adapter;
        $this->redisConfig = $redisConfig;
    }

    public function get(): CollectorRegistry
    {
        $adapter = env('PHP_METRIC_ADAPTER', 'in-memory');

        switch ($this->adapter) {
            case 'redis':
                $adapter = $this->redis();
                break;
            case 'apc':
                $adapter = new APC();
                break;
            case 'in-memory':
                $adapter = new InMemory();
                break;
            default :
                
        }

        return new CollectorRegistry($adapter);
    }

    private function redis(): Redis
    {
        $connect  = env('PHP_METRIC_REDIS_CONNECT', 'default');
        $database = env('PHP_METRIC_REDIS_DB', 2);

        $options = [
            'database' => (int) $database,
            'timeout'  => 0.1,
            'read_timeout' => 10,
        ] + config('database.redis.' . $connect);

        return new Redis($options);
    }
}