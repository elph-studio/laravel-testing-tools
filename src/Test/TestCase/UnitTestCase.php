<?php

declare(strict_types=1);

namespace Elph\LaravelTesting\Test\TestCase;

use Elph\LaravelTesting\Test\TestHelper\CreatesApplication;
use Illuminate\Foundation\Testing\TestCase;

/**
 * Use Unit tests for a single class or single public method testing.
 * - Mock database
 * - Mock any HTTP requests and other outgoing connections
 */
abstract class UnitTestCase extends TestCase
{
    use CreatesApplication;
}
