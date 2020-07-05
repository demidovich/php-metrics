<?php

namespace Metrics;

class Labels
{
    private $keys   = [];
    private $labels = [];

    public function __construct(array $labels)
    {
        foreach ($labels as $name => $value) {
            $this->set($name, $value);
        }
    }

    public function set(string $name, $value): void
    {
        $this->labels[$name] = $value;
    }

    public function all(): array
    {
        return $this->labels;
    }
}
