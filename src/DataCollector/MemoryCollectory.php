<?php

namespace Iffian\MemoryDebugbar\DataCollector;

use Closure;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use DebugBar\DebugBarException;

class MemoryDataCollector extends DataCollector implements Renderable
{

    /**
     * @var float
     */
    protected $requestStartMemory;

    /**
     * @var float
     */
    protected $requestEndMemory;

    /**
     * @var array
     */
    protected $startedMeasures = [];

    /**
     * @var array
     */
    protected $measures = [];


    protected $realUsage = false;

    protected $peakUsage = 0;

    /**
     * Returns whether total allocated memory page size is used instead of actual used memory size
     * by the application.  See $real_usage parameter on memory_get_usage for details.
     *
     * @return bool
     */
    public function getRealUsage()
    {
        return $this->realUsage;
    }

    /**
     * Sets whether total allocated memory page size is used instead of actual used memory size
     * by the application.  See $real_usage parameter on memory_get_usage for details.
     *
     * @param bool $realUsage
     */
    public function setRealUsage($realUsage)
    {
        $this->realUsage = $realUsage;
    }

    /**
     * Returns the peak memory usage
     *
     * @return integer
     */
    public function getPeakUsage()
    {
        return $this->peakUsage;
    }

    /**
     * Updates the peak memory usage value
     */
    public function updatePeakUsage()
    {
        $this->peakUsage = memory_get_usage($this->realUsage);
    }

    /**
     * Starts a measure
     *
     * @param string $name Internal name, used to stop the measure
     * @param string|null $label Public name
     * @param string|null $collector The source of the collector
     */
    public function startMeasure($name, $label = null, $collector = null)
    {
        $start = memory_get_usage(true);
        $this->startedMeasures[$name] = [
            'label' => $label ?: $name,
            'start' => $start,
            'collector' => $collector
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
     * @param array $params
     * @throws DebugBarException
     */
    public function stopMeasure($name, $params = [])
    {
        $end = memory_get_usage(true);
        if (!$this->hasStartedMeasure($name)) {
            throw new DebugBarException("Failed stopping measure '$name' because it hasn't been started");
        }
        $this->addMeasure(
            $this->startedMeasures[$name]['label'],
            $this->startedMeasures[$name]['start'],
            $end,
            $params,
            $this->startedMeasures[$name]['collector']
        );
        unset($this->startedMeasures[$name]);
    }

    /**
     * Adds a measure
     *
     * @param string $label
     * @param float $start
     * @param float $end
     * @param array $params
     * @param string|null $collector
     */
    public function addMeasure($label, $start, $end, $params = [], $collector = null)
    {
        $this->measures[] = [
            'label' => $label,
            'start' => $start,
            'relative_start' => $start - $this->requestStartMemory,
            'end' => $end,
            'relative_end' => $end - $this->requestEndMemory,
            'duration' => $end - $start,
            'duration_str' => $this->getDataFormatter()->formatBytes($end - $start),
            'params' => $params,
            'collector' => $collector
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
     * Returns the request start memory
     *
     * @return float
     */
    public function getRequestStartMemory()
    {
        return $this->requestStartMemory;
    }

    /**
     * Returns the request end memory
     *
     * @return float
     */
    public function getRequestEndMemory()
    {
        return $this->requestEndMemory;
    }

    /**
     * Returns the duration of a request
     *
     * @return float
     */
    public function getRequestDuration()
    {
        if ($this->requestEndMemory !== null) {
            return $this->requestEndMemory - $this->requestStartMemory;
        }
        return memory_get_usage(true) - $this->requestStartMemory;
    }

    /**
     * @return array
     * @throws DebugBarException
     */
    public function collect()
    {
        $this->updatePeakUsage();
        $this->requestEndMemory = memory_get_usage(true);
        foreach (array_keys($this->startedMeasures) as $name) {
            $this->stopMeasure($name);
        }

        usort($this->measures, function ($a, $b) {
            if ($a['start'] == $b['start']) {
                return 0;
            }
            return $a['start'] < $b['start'] ? -1 : 1;
        });

        return [
            'start' => $this->requestStartMemory,
            'end' => $this->requestEndMemory,
            'duration' => $this->getRequestDuration(),
            'duration_str' => $this->getDataFormatter()->formatBytes($this->getRequestDuration()),
            'peak_usage' => $this->peakUsage,
            'peak_usage_str' => $this->getDataFormatter()->formatBytes($this->peakUsage),
            'measures' => array_values($this->measures)
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
                "widget" => "PhpDebugBar.Widgets.TimelineWidget",
                "map" => "memory_details",
                "default" => "{}"
            ]
        ];
    }
}
