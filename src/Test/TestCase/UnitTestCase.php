<?php

declare(strict_types=1);

namespace Elph\LaravelTestingTools\Test\TestCase;

use Illuminate\Foundation\Testing\TestCase;
use Elph\LaravelTestingTools\Test\TestHelper\CreatesApplication;

abstract class UnitTestCase extends TestCase
{
    use CreatesApplication;
}
