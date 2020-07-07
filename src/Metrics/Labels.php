<?php

namespace Metrics;

class Labels
{
    private $labels = [];

    public function __construct(array $labels)
    {
        $this->labels = $labels;
    }

    public function all(): array
    {
        return $this->labels;
    }

    public function with(array $labels): array
    {
        return $this->labels + $labels;
    }
}
