<?php

declare(strict_types=1);

namespace Elph\LaravelTestingTools\Test\TestCase;

use Elph\LaravelTestingTools\Test\TestHelper\CreatesApplication;
use Illuminate\Foundation\Testing\TestCase;

abstract class UnitTestCase extends TestCase
{
    use CreatesApplication;
}
