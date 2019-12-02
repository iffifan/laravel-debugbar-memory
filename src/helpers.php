<?php

if (!function_exists('start_memory_measure')) {
    /**
     * Starts a measure
     *
     * @param string $name Internal name, used to stop the measure
     * @param string $label Public name
     */
    function start_memory_measure($name, $label = null)
    {
        try {
            debugbar()
                ->getCollector('memory_details')
                ->startMeasure($name, $label);
        } catch (\DebugBar\DebugBarException $exception) {
            report($exception);
        }
    }
}

if (!function_exists('stop_memory_measure')) {
    /**
     * Stop a measure
     *
     * @param string $name Internal name, used to stop the measure
     */
    function stop_memory_measure($name)
    {
        try {
            debugbar()
                ->getCollector('memory_details')
                ->stopMeasure($name);
        } catch (\DebugBar\DebugBarException $exception) {
            report($exception);
        }
    }
}
