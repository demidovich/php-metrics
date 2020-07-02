<?php

use Metric\MyMetric;
use Metric\MetricStorage;
use Psr\Log\LoggerInterface;

define('APP_START_TIME', hrtime(true));

usleep(10);

$metric = new MyMetric(APP_START_TIME, 'yazoo');

// Initialization of the metrics storage layer
// Metric can work without this functionality

if ($config['persist']) {
    $storage = MetricStorage::create('in-memory', [], $metric);
}

// Metrics can be logged

if ($config['debug']) {
    $storage->debug(
        new LoggerInterface()
    );
}

