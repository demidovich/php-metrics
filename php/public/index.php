<?php

require __DIR__.'/../vendor/autoload.php';

if ($_SERVER['REQUEST_URI'] == '/metrics') {
    require __DIR__.'/../app/MetricController.php';
} else {
    require __DIR__.'/../app/AppController.php';
}
