<?php

namespace Tests\Bokbasen\ApiClient;

use Bokbasen\ApiClient\Client;
use Bokbasen\ApiClient\Exceptions\BokbasenApiClientException;
use Bokbasen\Auth\Login;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\NullLogger;

class ClientTest extends TestCase
{
    private function getLogin()
    {
        $stub = $this->createMock(Login::class);
        $stub->method('getTgt')
            ->willReturn('TGT-123123-123123-123123');
        $stub->method('getAuthHeadersAsArray')
            ->willReturn([
                'Authorization' => 'Boknett TGT-123123-123123-123123',
                'Date' => gmdate(Login::HTTP_HEADER_DATE_FORMAT)
            ]);

        return $stub;
    }

    public function testSetLogger()
    {
        $client = new Client($this->getLogin(), 'http://client.test');

        $logger = new Logger('first', [$handler = new TestHandler()]);
        $client->setLogger($logger);

        $client->get('/path');
        list($record) = $handler->getRecords();

        $this->assertEquals(
            'Executing HTTP GET request to http://client.test/path with data .',
            $record['message']
        );
    }

    public function testGet()
    {
        $client = new Client($this->getLogin(), 'http://client.test');

        $response = $client->get('/getpath');
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $response = $client->get('/getpath', ["Content-Type" => "application/json"],false);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testPost()
    {
        $client = new Client($this->getLogin(), 'http://client.test');

        $response = $client->post('/path', json_encode(['my' => 'body']));
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $response = $client->post('/path', json_encode(['my' => 'body']), ["Content-Type" => "application/json"],false);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testPut()
    {
        $client = new Client($this->getLogin(), 'http://client.test');

        $response = $client->put('/path', json_encode(['my' => 'body']));
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $response = $client->put('/path', json_encode(['my' => 'body']), ["Content-Type" => "application/json"],false);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testPatch()
    {
        $client = new Client($this->getLogin(), 'http://client.test');

        $response = $client->patch('/path', json_encode(['my' => 'body']));
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $response = $client->patch('/path', json_encode(['my' => 'body']), ["Content-Type" => "application/json"],false);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testPostJson()
    {
        $client = new Client($this->getLogin(), 'http://client.test');

        $response = $client->postJson('/path', ['my' => 'body']);
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $response = $client->postJson('/path', (object) ['my' => 'body'], ["Content-Type" => "application/json"],false);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testPostJsonInvalidArgument()
    {
        $this->expectException(BokbasenApiClientException::class);

        $client = new Client($this->getLogin(), 'http://client.test');
        $client->postJson('/path', "invalid body");
    }

    public function testClientException()
    {
        $this->expectException(BokbasenApiClientException::class);

        $client = new Client($this->getLogin(), '////');
        $client->get('???');
    }
}

