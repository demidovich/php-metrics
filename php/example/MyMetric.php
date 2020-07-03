<?php

namespace Metric;

use Metric\Metric;

class MyMetric extends Metric
{
    public function spentSql(int $nanoseconds): void
    {
        $this->spent('sql', $nanoseconds);
    }

    public function startRedis(): void
    {
        $this->start('redis');
    }

    public function startCall(): void
    {
        $this->start('call');
    }
}