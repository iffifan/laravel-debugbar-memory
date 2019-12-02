<?php namespace Iffifan\MemoryDebugbar\Providers;

use Barryvdh\Debugbar\LaravelDebugbar;
use Iffian\MemoryDebugbar\DataCollector\MemoryDataCollector;
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
        $debugbar = $this->app(LaravelDebugbar::class);
        if ($debugbar->shouldCollect('memory_details', true)) {
            $debugbar->addCollector(new MemoryDataCollector());
        }
}
}
