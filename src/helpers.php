<?php

/**
 * Dump the items and end the script.
 *
 * @param  mixed  ...$args
 * @return never
 */
function dd(...$args)
{
    echo PHP_EOL;
    foreach ($args as $arg) {
        print_r($arg);
        echo PHP_EOL;
    }
    echo PHP_EOL;

    exit(1);
}
