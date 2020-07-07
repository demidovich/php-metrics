<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Metrics\Labels;

class LabelsTest extends TestCase
{
    public function test_construct()
    {
        $instance = new Labels(['node' => '10.0.0.1']);

        $values = $instance->all();

        $this->assertArrayHasKey('node', $values);
        $this->assertEquals('10.0.0.1', $values['node']);
    }

    public function test_append_labels()
    {
        $instance = new Labels(['node' => '10.0.0.1']);

        $values = $instance->with(['timer' => 'php']);

        $this->assertArrayHasKey('node', $values);
        $this->assertEquals('10.0.0.1', $values['node']);

        $this->assertArrayHasKey('node', $values);
        $this->assertEquals('10.0.0.1', $values['node']);

        $this->assertArrayHasKey('timer', $values);
        $this->assertEquals('php', $values['timer']);
    }
}
