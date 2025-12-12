<?php

declare(strict_types=1);

namespace Elph\LaravelTesting\Test\TestHelper;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use RuntimeException;

// phpcs:disable
trait CreatesApplication
{
    private array $possibleAppLocations = [
        'bootstrap/app.php', // Application testing
        'vendor/elph-studio/laravel-testing-tools/src/Test/bootstrap.php', // Library testing
        'src/Test/bootstrap.php', // Testing tools library testing
    ];

    public function createApplication(): Application
    {
        $app = $this->getAppLocation();

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    private function getAppLocation(): Application
    {
        foreach ($this->possibleAppLocations as $location) {
            if (file_exists($location) === true) {
                return require $location;
            }
        }

        throw new RuntimeException('Application not found');
    }
}
