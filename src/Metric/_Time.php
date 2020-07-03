<?php

namespace Metric;

class _Time
{
    const UNIT_NANOSECOND  = 1;
    const UNIT_MICROSECOND = 2;
    const UNIT_MILLISECOND = 3;
    const UNIT_SECOND      = 4;

    private $value;
    private $unit;

    public final function __construct($value, int $unit)
    {
        $this->value = $value;
        $this->unit  = $unit;
    }

    public function toNanosecond(): int
    {
        
    }
}