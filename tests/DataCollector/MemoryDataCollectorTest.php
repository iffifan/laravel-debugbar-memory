<?php namespace Iffifan\MemoryDebugbar\Tests\DataCollector;

use Iffifan\MemoryDebugbar\DataCollector\MemoryDataCollector;
use Iffifan\MemoryDebugbar\Tests\TestCase;

/**
 * Class MemoryDataCollectorTest
 *
 * @package Iffifan\MemoryDebugbar\Tests\DataCollector
 */
class MemoryDataCollectorTest extends TestCase
{
    protected $collector;

    public function setUp()
    {
        parent::setUp();
        $this->collector = new MemoryDataCollector();
    }

    public function testAddMeasure()
    {
        $this->collector->addMeasure('test', 1, 2);
        $mesures = $this->collector->getMeasures();
        $this->assertArrayHasKey('test', $mesures);
        $this->assertArrayHasKey('start', $mesures['test']);
        $this->assertArrayHasKey('end', $mesures['test']);
    }

    public function testStartMeasure()
    {
        $this->collector->startMeasure('test');
        $this->assertTrue($this->collector->hasStartedMeasure('test'));
    }

    public function testStopMeasure()
    {
        $this->collector->startMeasure('test');
        $this->collector->stopMeasure('test');
        $mesures = $this->collector->getMeasures();
        $this->assertArrayHasKey('test', $mesures);
        $this->assertArrayHasKey('start', $mesures['test']);
        $this->assertArrayHasKey('end', $mesures['test']);
    }

    public function testCollect()
    {
        $this->collector->startMeasure('test');
        $this->collector->stopMeasure('test');
        $data = $this->collector->collect();
        $this->assertArrayHasKey('measures', $data);
        $this->assertArrayHasKey('test', $data['measures']);
    }
}
