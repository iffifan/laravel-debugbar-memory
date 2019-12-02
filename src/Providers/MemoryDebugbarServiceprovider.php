<?php namespace Iffifan\MemoryDebugbar\Providers;

use Barryvdh\Debugbar\LaravelDebugbar;
use Iffifan\MemoryDebugbar\DataCollector\MemoryDataCollector;
use Illuminate\Support\ServiceProvider;

/**
 * Class MemoryDebugbarServiceprovider
 *
 * @package Iffifan\MemoryDebugbar\Providers
 */
class MemoryDebugbarServiceprovider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register()
    {
        $debugbar = $this->app->make(LaravelDebugbar::class);
        if ($debugbar->shouldCollect('memory_details', true)) {
            $debugbar->addCollector(new MemoryDataCollector());
            $this->app->booted(
                static function () use ($debugbar) {
                    $debugbar['memory_details']->addMeasure('Booting', 0, memory_get_peak_usage(false));
                    $debugbar['memory_details']->startMeasure('Application');
                }
            );
        }
    }
}
