<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;

$basepath = '/tmp';
$bootstrapCache = $basepath . '/bootstrap/cache';

// phpcs:ignore Generic.PHP.ForbiddenFunctions
if (is_dir($bootstrapCache) === false) {
    mkdir($bootstrapCache, 0755, true);
}

return Application::configure($basepath)
    ->withRouting(using: static fn () => Route::get('/', static fn () => 'ok')) // ExampleControllerTest
    ->create();
