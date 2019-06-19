<?php

namespace Tests\Bokbasen\ApiClient;

use Bokbasen\ApiClient\Caller;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;

class CallerTest extends TestCase
{
    public function testRequest()
    {
        $caller = new Caller(new Client());
        $response = $caller->request('GET', 'https://example.com');

        $this->assertEquals(
            200,
            $response->getStatusCode()
        );
    }
}
