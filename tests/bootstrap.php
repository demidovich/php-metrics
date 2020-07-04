<?php

apcu_clear_cache();

$file = __DIR__.'/../vendor/autoload.php';

if (! file_exists($file)) {
    throw new RuntimeException('Install composer dependencies to run test suite');
}

if (! function_exists('hrtime')) {
    throw new RuntimeException('Install PHP >=7.3 or extension https://pecl.php.net/package/hrtime');
}

require_once $file;