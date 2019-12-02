# laravel-debugbar-memory
[![License](https://poser.pugx.org/iffifan/laravel-debugbar-memory/license)](https://packagist.org/packages/iffifan/laravel-debugbar-memory)

Add detailed memory usage measurement for code blocks in [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar)



## Installation

Require this package with composer. It is recommended to only require the package for development.

```shell
composer require iffifan/laravel-debugbar-memory --dev
```

Laravel 5.5 uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

If you don't use auto-discovery, add the ServiceProvider to the providers array in config/app.php

```php
Iffifan\MemoryDebugbar\Providers\MemoryDebugbarServiceProvider::class,
```
## Usage

After successful installation you should see `Memory` tab in your Debugbar

![Screenshot](https://i.ibb.co/hHHbnVZ/debugbar-memory.jpg)

### Measuring memory usage of a code block

Let's calculate memory usage of a while loop with helper methods

```php
    start_memory_measure('Some Loop');
    $a = 0;
    $b = 'X';
    while ($a < 10000000) {
        $b .= 'X';
        ++$a;
    }
    stop_memory_measure('Some Loop');
```
Memory calculation will be updated like this:

![Screenshot](https://i.ibb.co/gryfYkY/debugbar-memory-code.jpg)
