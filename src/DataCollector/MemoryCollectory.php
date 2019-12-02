<?php

namespace Iffifan\MemoryDebugbar\DataCollector;

use Closure;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use DebugBar\DebugBarException;

/**
 * Class MemoryDataCollector
 *
 * @package Iffifan\MemoryDebugbar\DataCollector
 */
class MemoryDataCollector extends DataCollector implements Renderable
{

    /**
     * @var array
     */
    protected $startedMeasures = [];

    /**
     * @var array
     */
    protected $measures = [];


    protected $realUsage = false;

    /**
     * Returns whether total allocated memory page size is used instead of actual used memory size
     * by the application.  See $real_usage parameter on memory_get_peak_usage for details.
     *
     * @return bool
     */
    public function getRealUsage()
    {
        return $this->realUsage;
    }

    /**
     * Sets whether total allocated memory page size is used instead of actual used memory size
     * by the application.  See $real_usage parameter on memory_get_peak_usage for details.
     *
     * @param bool $realUsage
     */
    public function setRealUsage($realUsage)
    {
        $this->realUsage = $realUsage;
    }

    /**
     * Starts a measure
     *
     * @param string $name Internal name, used to stop the measure
     * @param string|null $label Public name
     */
    public function startMeasure($name, $label = null)
    {
        $start = memory_get_peak_usage($this->realUsage);
        $this->startedMeasures[$name] = [
            'start' => $start
        ];
    }

    /**
     * Check a measure exists
     *
     * @param string $name
     * @return bool
     */
    public function hasStartedMeasure($name)
    {
        return isset($this->startedMeasures[$name]);
    }

    /**
     * Stops a measure
     *
     * @param string $name
     * @throws DebugBarException
     */
    public function stopMeasure($name)
    {
        $end = memory_get_peak_usage($this->realUsage);
        if (!$this->hasStartedMeasure($name)) {
            throw new DebugBarException("Failed stopping measure '$name' because it hasn't been started");
        }
        $this->addMeasure(
            $name,
            $this->startedMeasures[$name]['start'],
            $end
        );
        unset($this->startedMeasures[$name]);
    }

    /**
     * Adds a measure
     *
     * @param string $label
     * @param float $start
     * @param float $end
     */
    public function addMeasure($label, $start, $end)
    {
        $this->measures[$label] = [
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * Utility function to measure the execution of a Closure
     *
     * @param string $label
     * @param Closure $closure
     * @param string|null $collector
     * @throws DebugBarException
     */
    public function measure($label, Closure $closure, $collector = null)
    {
        $name = spl_object_hash($closure);
        $this->startMeasure($name, $label, $collector);
        $result = $closure();
        $params = is_array($result) ? $result : [];
        $this->stopMeasure($name, $params);
    }

    /**
     * Returns an array of all measures
     *
     * @return array
     */
    public function getMeasures()
    {
        return $this->measures;
    }

    /**
     * @return array
     * @throws DebugBarException
     */
    public function collect()
    {
        foreach (array_keys($this->startedMeasures) as $name) {
            $this->stopMeasure($name);
        }
        $measures = [];
        foreach ($this->measures as $name => $measure) {
            $measures[$name] = $this->getDataFormatter()->formatBytes($measure['end'] - $measure['start']);
        }
        return [
            'measures' => $measures
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'memory_details';
    }

    /**
     * @return array
     */
    public function getWidgets()
    {
        return [
            "memory_details" => [
                "icon" => "tasks",
                "title" => "Memory",
                "widget" => "PhpDebugBar.Widgets.HtmlVariableListWidget",
                "map" => "memory_details.measures",
                "default" => "{}"
            ]
        ];
    }
}
