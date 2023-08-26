<?php

namespace Metrics;

class Labels
{
    private array $labels = [];

    public function __construct(array $labels)
    {
        $this->labels = $labels;
    }

    public function all(): array
    {
        return $this->labels;
    }

    public function allWith(array $labels): array
    {
        return $this->labels + $labels;
    }
}
