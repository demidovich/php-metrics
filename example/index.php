<?php

define('APP_START_TIME', hrtime(true));

require __DIR__.'/../vendor/autoload.php';

$metric = new MyMetrics(APP_START_TIME);
$storage = Metrics\Storage::create('in-memory', [], $metric);

// PHP initialization is complete.
// Start of business logic 
$metric->startPhp();

if ($_SERVER['REQUEST_URI'] == '/metrics') {
    echo exportMetricsHandler($metric);
} else {
    echo appHandler($metric);
}

function appHandler(MyMetrics $metric)
{
    // Register runtime for redis query
    $metric->startRedis();
    usleep(10);

    // Some business logic
    $metric->startPhp();
    usleep(10);

    // Register runtime for external call
    $metric->startCall();
    usleep(10);

    // Some business logic
    $metric->startPhp();
    usleep(100);

    // Adding the spent time
    // E.g. inside the database query event
    // These 10 microseconds will be subtracted from the 100 microseconds 
    // of the current php tracking
    $metric->spentSql(10);

    // Register a business logic event
    $metric->signinAttemptEvent();

    // These times and events will be stored in register_shutdown_function()
}

function exportMetricsHandler(Metrics\Storage $storage)
{
    echo $storage->fetch();
}