<?php

use Metric\Metric;

class MyMetric extends Metric
{
    public function spentSql(int $microseconds): void
    {
        $this->spent('sql', (int) $microseconds * 1000);
    }

    public function startRedis(): void
    {
        $this->start('redis');
    }

    public function startCall(): void
    {
        $this->start('call');
    }

    public function signilAttemptEvent(): void
    {
        $this->incrCounter('signin_attempt');
    }
}