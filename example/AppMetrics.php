<?php

use Metrics\Metrics;
use Metrics\Timer;

/**
 * Wrappers of your counters and timers
 */
class AppMetrics extends Metrics
{
    protected $namespace = "myapp";

    /**
     * Register spent time from database query event or etc
     * 
     * @param int $microseconds
     * @return void
     */
    public function spentSql(int $microseconds): void
    {
        $timer = Timer::stoppedFromMicroseconds("sql", $microseconds);

        $this->runtime()->spent($timer);
    }

    /**
     * Start timer of redis query
     * 
     * @return void
     */
    public function startRedis(): void
    {
        $this->runtime()->start("redis");
    }

    /**
     * Start timer of remote call
     * 
     * @return void
     */
    public function startRemoteCall(): void
    {
        $this->runtime()->start("remote_call");
    }

    /**
     * Increase of sigin attempts counter
     * 
     * @return void
     */
    public function registerSigninAttempt(): void
    {
        $this->counters()->increase("signin_attempt");
    }
}