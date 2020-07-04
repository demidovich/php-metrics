<?php

namespace Metrics;

class Counters
{
    private $counters = [];

    public function increase(string $name, int $quantity = 1): void
    {
        if (! isset($this->counters[$name])) {
            $this->counters[$name] = 0;
        }

        $this->counters[$name] += $quantity;
    }

    public function all(): array
    {
        return $this->counters;
    }

}