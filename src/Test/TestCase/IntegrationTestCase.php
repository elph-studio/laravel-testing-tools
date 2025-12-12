<?php

declare(strict_types=1);

namespace Elph\LaravelTesting\Test\TestCase;

use Elph\LaravelTesting\Test\TestHelper\CreatesApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;

/**
 * Use Integration tests for interaction between multiple components testing.
 * - Mock database or separate repositories if they are not required for testing purposes.
 * - Mock any outgoing HTTP requests and other outgoing connections.
 */
abstract class IntegrationTestCase extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;
}
