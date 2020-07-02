<?php

namespace Metric1;

use Metric1\Metric;
use Psr\Log\LoggerInterface;

class MetricLogger
{
    private $logger;
    private $metric;

    public function __construct(LoggerInterface $logger, Metric $metric)
    {
        $this->logger = $logger;
        $this->metric = $metric;
    }

    public function log(): void
    {
        $this->logger->info('metric labels : '.print_r($this->metric->labels, true));
        $this->logger->info('metric timers : '.print_r($this->metric->timersInMilliseconds(), true));
        $this->logger->info('metric memory : '.print_r($this->metric->memoryUsage(), true));
        $this->logger->info('metric counters : '.print_r($this->metric->counters(), true));
    }
}