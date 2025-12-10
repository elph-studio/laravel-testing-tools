<?php

declare(strict_types=1);

namespace Tests\Controller;

use Elph\LaravelTestingTools\Test\TestCase\ControllerTestCase;

class ExampleControllerTest extends ControllerTestCase
{
    /**
     * A basic test example.
     */
    public function testApplicationReturnsSuccessfulResponse(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertContent('ok');
    }
}
