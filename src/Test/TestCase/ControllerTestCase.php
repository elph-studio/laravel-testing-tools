<?php

declare(strict_types=1);

namespace PhpNotDead\LaravelTestingTools\Test\TestCase;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use PhpNotDead\LaravelTestingTools\Test\TestHelper\CreatesApplication;

abstract class ControllerTestCase extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;
}
