<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\AppMetric;

class MetricTest extends TestCase
{
    private function metric(int $initMicroseconds = null): AppMetric
    {
        $startTime = hrtime(true);

        if ($initMicroseconds) {
            usleep($initMicroseconds);
        }

        return new AppMetric($startTime);
    }

    public function test_metric_namespace()
    {
        $metric = $this->metric();

        $this->assertEquals('myapp', $metric->namespace());
    }
}