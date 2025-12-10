<?php

declare(strict_types=1);

namespace Tests\Helper;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class ComposerLockBuilder
{
    private const string SOURCE_COMPOSER_LOCATION = 'tests/Fixtures/composer.lock.source';
    private const string TEMPORARY_COMPOSER_LOCATION = '/tmp/composer.lock';

    private array $fixturePackages;

    /**
     * @param array<array{name:string, version:string}> $packages
     * @param array<array{name:string, version:string}> $packagesDev
     * @return string
     */
    public function build(array $packages, array $packagesDev): string
    {
        $fixtureJson = File::get(self::SOURCE_COMPOSER_LOCATION);
        $fixture = json_decode($fixtureJson, true, 512, JSON_THROW_ON_ERROR);

        $composerLock = $fixture;
        $composerLock['packages'] = $this->generatePackagesInfo($packages, $fixture);
        $composerLock['packages-dev'] = $this->generatePackagesInfo($packagesDev, $fixture);
        $composerLock['stability-flags'] = $this->generateStabilityFlags($composerLock);

        $json = json_encode($composerLock, JSON_THROW_ON_ERROR);

        file_put_contents(self::TEMPORARY_COMPOSER_LOCATION, $json);

        return self::TEMPORARY_COMPOSER_LOCATION;
    }

    /**
     * @param array{name: 'string', version: 'string'} $packages
     * @param array $fixture
     * @return array
     */
    private function generatePackagesInfo(array $packages, array $fixture): array
    {
        if (empty($packages) === true) {
            return [];
        }

        if (isset($this->fixturePackages) === false) {
            $this->generateFixturePackages($fixture);
        }

        $result = [];
        collect($packages)->each(function (array $package) use (&$result) {
            $packageInfo = Arr::exists($this->fixturePackages, $package['name']) === true
                ? $this->fixturePackages[$package['name']]
                : $this->fixturePackages[array_rand($this->fixturePackages)];

            $packageInfo['name'] = $package['name'];
            $packageInfo['version'] = $package['version'];

            $result[] = $packageInfo;
        });

        return $result;
    }

    private function generateStabilityFlags(array $composerLock): array
    {
        $unstablePackages = [];

        collect($composerLock['packages'])
            ->merge($composerLock['packages-dev'])
            ->filter(static function (array $package) use (&$unstablePackages) {
                $pass = preg_match('/^v?\d{1,2}\.\d{1,4}(?:\.\d{1,4})?$/', $package['version']) === 1;
                if ($pass === true) {
                    return;
                }

                $unstablePackages[$package['name']] = 20;
            });

        return $unstablePackages;
    }

    private function generateFixturePackages(array $fixture): void
    {
        collect($fixture['packages'])
            ->merge($fixture['packages-dev'])
            ->each(function (array $package) {
                $this->fixturePackages[$package['name']] = $package;
            });
    }
}
