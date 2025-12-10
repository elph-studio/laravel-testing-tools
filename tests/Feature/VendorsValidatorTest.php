<?php

declare(strict_types=1);

namespace Tests\Feature;

use Elph\LaravelTestingTools\Test\TestCase\FeatureTestCase;
use Generator;
use JetBrains\PhpStorm\ArrayShape;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Helper\ComposerLockBuilder;

class VendorsValidatorTest extends FeatureTestCase
{
    private ComposerLockBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = app(ComposerLockBuilder::class);
    }

    #[DataProvider('generateDataForSuccessfulVendorsValidation')]
    public function testWillRunVendorsValidatorAndPassWithoutWarnings(
        array $packages,
        array $packagesDev,
        int $expectedSuccesses
    ): void {
        // Data
        $composer = $this->builder->build($packages, $packagesDev);

        // Execution
        exec(
            'php /app/src/Helper/vendors_validator.php --composer=' . $composer,
            $output,
            $exitCode
        );

        $results = $this->countResults($output);

        // Assertions
        $this->assertEquals(0, $exitCode);
        $this->assertEquals($expectedSuccesses, $results['success']);
        $this->assertEquals(0, $results['warning']);
        $this->assertEquals(0, $results['error']);
    }

    public static function generateDataForSuccessfulVendorsValidation(): Generator
    {
        yield 'prod & dev packages' => [
            'packages' => [
                ['name' => 'elph-studio/laravel-helpers', 'version' => 'v1.0.0'],
                ['name' => 'elph-studio/laravel-database-tools', 'version' => 'v1.0.0'],
            ],
            'packagesDev' => [
                ['name' => 'elph-studio/laravel-testing-tools', 'version' => 'v1.0.0'],
            ],
            'expectedSuccesses' => 3,
        ];

        yield 'prod packages only' => [
            'packages' => [
                ['name' => 'elph-studio/laravel-helpers', 'version' => 'v1.0.0'],
                ['name' => 'elph-studio/laravel-database-tools', 'version' => 'v1.0.0'],
            ],
            'packagesDev' => [],
            'expectedSuccesses' => 2,
        ];

        yield 'dev packages only' => [
            'packages' => [],
            'packagesDev' => [
                ['name' => 'elph-studio/laravel-helpers', 'version' => 'v1.0.0'],
                ['name' => 'elph-studio/laravel-database-tools', 'version' => 'v1.0.0'],
            ],
            'expectedSuccesses' => 2,
        ];

        yield 'no packages' => [
            'packages' => [],
            'packagesDev' => [],
            'expectedSuccesses' => 0,
        ];
    }

    #[DataProvider('generateDataForSuccessfulVendorsValidationWithWarnings')]
    public function testWillRunVendorsValidatorAndPassWithWarnings(
        array $packages,
        array $packagesDev,
        array $skip,
        int $expectedSuccesses,
        int $expectedWarnings,
    ): void {
        // Data
        $composer = $this->builder->build($packages, $packagesDev);

        // Execution
        exec(
            sprintf(
                'php /app/src/Helper/vendors_validator.php --composer=%s --skip=%s',
                $composer,
                implode(',', $skip)
            ),
            $output,
            $exitCode
        );

        $results = $this->countResults($output);

        // Assertions
        $this->assertEquals(0, $exitCode);
        $this->assertEquals($expectedSuccesses, $results['success']);
        $this->assertEquals($expectedWarnings, $results['warning']);
        $this->assertEquals(0, $results['error']);
    }

    public static function generateDataForSuccessfulVendorsValidationWithWarnings(): Generator
    {
        yield 'prod & dev packages' => [
            'packages' => [
                ['name' => 'elph-studio/laravel-helpers', 'version' => 'dev-testing'],
                ['name' => 'elph-studio/laravel-database-tools', 'version' => 'v1.0.0'],
            ],
            'packagesDev' => [
                ['name' => 'elph-studio/laravel-testing-tools', 'version' => 'v1.0.0'],
            ],
            'skip' => [
                'elph-studio/laravel-helpers',
            ],
            'expectedSuccesses' => 2,
            'expectedWarnings' => 1,
        ];

        yield 'prod & dev several packages' => [
            'packages' => [
                ['name' => 'elph-studio/laravel-helpers', 'version' => 'dev-testing'],
                ['name' => 'elph-studio/laravel-database-tools', 'version' => 'v1.0.0'],
            ],
            'packagesDev' => [
                ['name' => 'elph-studio/laravel-testing-tools', 'version' => 'dev-master'],
            ],
            'skip' => [
                'elph-studio/laravel-helpers',
                'elph-studio/laravel-testing-tools',
            ],
            'expectedSuccesses' => 1,
            'expectedWarnings' => 2,
        ];

        yield 'prod packages only' => [
            'packages' => [
                ['name' => 'elph-studio/laravel-helpers', 'version' => 'v1.0.0'],
                ['name' => 'elph-studio/laravel-database-tools', 'version' => 'dev-master'],
            ],
            'packagesDev' => [],
            'skip' => [
                'elph-studio/laravel-database-tools',
            ],
            'expectedSuccesses' => 1,
            'expectedWarnings' => 1,
        ];

        yield 'dev packages only' => [
            'packages' => [],
            'packagesDev' => [
                ['name' => 'elph-studio/laravel-helpers', 'version' => 'v1.0.0'],
                ['name' => 'elph-studio/laravel-database-tools', 'version' => 'dev-master'],
            ],
            'skip' => [
                'elph-studio/laravel-database-tools',
            ],
            'expectedSuccesses' => 1,
            'expectedWarnings' => 1,
        ];
    }

    #[DataProvider('generateDataForFailureVendorsValidation')]
    public function testWillRunVendorsValidatorAndFail(
        array $packages,
        array $packagesDev,
        array $skip,
        int $expectedSuccesses,
        int $expectedWarnings,
        int $expectedErrors,
    ): void {
        // Data
        $composer = $this->builder->build($packages, $packagesDev);

        // Execution
        exec(
            sprintf(
                'php /app/src/Helper/vendors_validator.php --composer=%s --skip=%s',
                $composer,
                implode(',', $skip)
            ),
            $output,
            $exitCode
        );

        $results = $this->countResults($output);

        // Assertions
        $this->assertEquals(255, $exitCode);
        $this->assertEquals($expectedSuccesses, $results['success']);
        $this->assertEquals($expectedWarnings, $results['warning']);
        $this->assertEquals($expectedErrors, $results['error']);
    }

    public static function generateDataForFailureVendorsValidation(): Generator
    {
        yield 'prod & dev packages' => [
            'packages' => [
                ['name' => 'elph-studio/laravel-helpers', 'version' => 'dev-testing'],
                ['name' => 'elph-studio/laravel-database-tools', 'version' => 'dev-testing'],
            ],
            'packagesDev' => [
                ['name' => 'elph-studio/laravel-testing-tools', 'version' => 'v1.0.0'],
            ],
            'skip' => [
                'elph-studio/laravel-helpers',
            ],
            'expectedSuccesses' => 1,
            'expectedWarnings' => 1,
            'expectedErrors' => 1,
        ];

        yield 'prod & dev several packages' => [
            'packages' => [
                ['name' => 'elph-studio/laravel-helpers', 'version' => 'dev-testing'],
                ['name' => 'elph-studio/laravel-database-tools', 'version' => 'dev-test'],
            ],
            'packagesDev' => [
                ['name' => 'elph-studio/laravel-testing-tools', 'version' => 'dev-master'],
            ],
            'skip' => [
                'elph-studio/laravel-helpers',
                'elph-studio/laravel-testing-tools',
            ],
            'expectedSuccesses' => 0,
            'expectedWarnings' => 2,
            'expectedErrors' => 1,
        ];

        yield 'prod packages only' => [
            'packages' => [
                ['name' => 'elph-studio/laravel-helpers', 'version' => 'v1.0.0'],
                ['name' => 'elph-studio/laravel-database-tools', 'version' => 'dev-master'],
            ],
            'packagesDev' => [],
            'skip' => [],
            'expectedSuccesses' => 1,
            'expectedWarnings' => 0,
            'expectedErrors' => 1,
        ];

        yield 'dev packages only' => [
            'packages' => [],
            'packagesDev' => [
                ['name' => 'elph-studio/laravel-helpers', 'version' => 'dev-test'],
                ['name' => 'elph-studio/laravel-database-tools', 'version' => 'dev-master'],
            ],
            'skip' => [
                'elph-studio/laravel-database-tools',
            ],
            'expectedSuccesses' => 0,
            'expectedWarnings' => 1,
            'expectedErrors' => 1,
        ];
    }

    public function testWillFailFindingComposerLock(): void
    {
        // Execution
        exec(
            'php /app/src/Helper/vendors_validator.php --composer=non_existing_composer.lock',
            $output,
            $exitCode
        );

        // Assertions
        $this->assertEquals(255, $exitCode);
        $this->assertCount(
            1,
            array_filter($output, static fn ($line) => str_contains($line, 'Custom composer.lock file not found'))
        );
    }

    #[ArrayShape([
        'success' => 'int',
        'warning' => 'int',
        'error' => 'int',
    ])]
    private function countResults(array $output): array
    {
        $result = [];

        $result['success'] = count(
            array_filter($output, static fn ($line) => str_contains($line, 'is production ready'))
        );

        $result['warning'] = count(
            array_filter($output, static fn ($line) => str_contains($line, 'is skipped by configuration'))
        );

        $result['error'] = count(
            array_filter($output, static fn ($line) => str_contains($line, 'is not production ready'))
        );

        return $result;
    }
}
