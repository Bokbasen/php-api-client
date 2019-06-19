<?php

namespace Tests\Bokbasen\ApiClient;

use Bokbasen\ApiClient\Client;
use Bokbasen\ApiClient\Exceptions\BokbasenApiClientException;
use Bokbasen\Auth\Login;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Http\Mock\Client as HttpClient;

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
                'Date'          => gmdate(Login::HTTP_HEADER_DATE_FORMAT),
            ]);

        return $stub;
    }

    private function getClient($url = 'http://client.test')
    {
        $client = new Client($this->getLogin(), $url);

        $httpClient = new HttpClient();
        $httpClient->addResponse(new Response(200, [], '{"body": "example"}'));
        $client->setHttpClient($httpClient);

        return $client;
    }

    public function testSetLogger()
    {
        $client = $this->getClient();

        $logger = new Logger('first', [$handler = new TestHandler()]);
        $client->setLogger($logger);

        $client->post('/path', json_encode(["data" => "data"]));
        list($request, $response) = $handler->getRecords();

        $this->assertEquals(
            '{"method":"POST","url":"http:\/\/client.test\/path","body":"{\"data\":\"data\"}"}',
            $request['message']
        );
        $this->assertEquals(
            '{"code":200,"headers":[]}',
            $response['message']
        );
    }

    public function testGet()
    {
        $client = $this->getClient();

        $response = $client->get('/getpath');
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $response = $client->get('/getpath', ["Content-Type" => "application/json"], false);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testPost()
    {
        $client = $this->getClient();

        $response = $client->post('/path', json_encode(['my' => 'body']));
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $response = $client->post('/path', json_encode(['my' => 'body']), ["Content-Type" => "application/json"], false);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testPut()
    {
        $client = $this->getClient();

        $response = $client->put('/path', json_encode(['my' => 'body']));
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $response = $client->put('/path', json_encode(['my' => 'body']), ["Content-Type" => "application/json"], false);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testPatch()
    {
        $client = $this->getClient();

        $response = $client->patch('/path', json_encode(['my' => 'body']));
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $response = $client->patch('/path', json_encode(['my' => 'body']), ["Content-Type" => "application/json"], false);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testPostJson()
    {
        $client = $this->getClient();

        $response = $client->postJson('/path', ['my' => 'body']);
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $response = $client->postJson('/path', ['my' => 'body'], ["Content-Type" => "application/json"], false);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testClientException()
    {
        $this->expectException(BokbasenApiClientException::class);

        $client = new Client($this->getLogin(), '???');

        $httpClient = new HttpClient();
        $httpClient->addException(new \Exception());
        $client->setHttpClient($httpClient);

        $client->get('???');
    }
}

