<?php

declare(strict_types=1);

namespace Elph\LaravelTestingTools\Test\TestHelper;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use RuntimeException;

trait CreatesApplication
{
    private array $possibleAppLocations = [
        'bootstrap/app.php',
        'tests/bootstrap.php',
    ];

    public function createApplication(): Application
    {
        $app = $this->getAppLocation();

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    private function getAppLocation(): Application
    {
        // phpcs:ignore
        foreach ($this->possibleAppLocations as $location) {
            if (file_exists($location) === true) {
                return require $location;
            }
        }

        throw new RuntimeException('Application not found');
    }
}
