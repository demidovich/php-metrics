<?php

namespace Metric;

class MetricLabel
{
    private $keys   = [];
    private $labels = [];

    public function set(string $name, $value): void
    {
        $this->labels[$name] = $value;
    }

    public function all(): array
    {
        return $this->labels;
    }

    public function keys(): array
    {
        if (! $this->keys) {
            $this->keys = array_keys($this->labels);
        }

        return $this->keys;
    }
}