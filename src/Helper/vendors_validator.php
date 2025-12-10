#!/usr/bin/env php
<?php

declare(strict_types=1);

// phpcs:disable

/**
 * This validator is used to ensure that only production ready vendors are required.
 * If any @dev version is used, exception will be thrown.
 *
 * Running: vendor/elph-studio/laravel-testing-tools/src/Helper/vendors_validator.php
 *
 * If you need to skip some vendors, add --skip={vendors list}
 * Example: vendor/elph-studio/laravel-testing-tools/src/Helper/vendors_validator.php --skip=test/first,test/second
 */
return new class () {
    private const string DEFAULT_COMPOSER_LOCATION = './composer.lock';

    public function __construct()
    {
        if ($this->validate() === false) {
            throw new RuntimeException('Workflow failed');
        }

        echo $this->color("\nSuccess! All vendors passed validation.", 'success');
    }

    private function getSkippedPackages(): array
    {
        $options = getopt('', ['skip:']);

        if (empty($options['skip']) === true) {
            return [];
        }

        $skip = explode(',', $options['skip']);
        $skip = array_filter($skip, static fn ($value) => trim($value) !== '');

        return array_combine($skip, $skip);
    }

    private function validate(): bool
    {
        $composer = $this->getComposer();
        if ($composer === null) {
            return false;
        }

        $packages = $this->getPackages($composer);
        if (count($packages) === 0) {
            return true;
        }

        $skip = $this->getSkippedPackages();

        $pass = true;
        foreach ($packages as $vendor => $version) {
            if ($this->validateVendor($vendor, $version, $skip) === true) {
                continue;
            }

            $pass = false;
        }

        return $pass;
    }

    private function getComposer(): array|null
    {
        $options = getopt('', ['composer:']);
        if (empty($options['composer']) !== true) {
            $composer = explode(',', $options['composer'])[0];
        }

        $composer ??= self::DEFAULT_COMPOSER_LOCATION;
        if (file_exists($composer) === false) {
            echo $this->color('composer.lock file not found' . "\n", 'success');

            return null;
        }

        $content = file_get_contents($composer);

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    private function getPackages(array $composer): array
    {
        $packages = $composer['packages'];
        if (array_key_exists('packages-dev', $composer)) {
            $packages = array_merge($packages, $composer['packages-dev']);
        }

        $vendors = [];
        foreach ($packages as $package) {
            $vendors[$package['name']] = $package['version'];
        }

        return $vendors;
    }

    private function validateVendor(string $vendor, string $version, array $skip): bool
    {
        /*
         * Allow:
         *   v0.0.0 - v99.9999.9999
         *   0.0.0 - 99.9999.9999
         *   v0.0 - v99.9999
         *   0.0 - 99.9999
         */
        $pass = preg_match('/^v?\d{1,2}\.\d{1,4}(?:\.\d{1,4})?$/', $version) === 1;
        if ($pass === true) {
            echo $this->color(
                sprintf('Vendor "%s:%s" is production ready.', $vendor, $version) . "\n",
                'success'
            );

            return true;
        }

        if (array_key_exists($vendor, $skip) === true) {
            echo $this->color(
                sprintf('Vendor "%s:%s" is skipped by configuration.', $vendor, $version) . "\n",
                'warning'
            );

            return true;
        }

        echo $this->color(
            sprintf('Vendor "%s:%s" is not production ready.', $vendor, $version) . "\n",
            'error'
        );

        return false;
    }

    private function color(string $text, string $type = 'success'): string
    {
        $color = match ($type) {
            'error' => 31, // red
            'success' => 32, // green
            'warning' => 33, // yellow
            default => 97, // white
        };

        return sprintf("\e[%dm%s\e[39m", $color, $text);
    }
};
