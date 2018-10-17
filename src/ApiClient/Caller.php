<?php

namespace Bokbasen\ApiClient;

use Bokbasen\ApiClient\Exceptions\BokbasenApiClientException;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Caller
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @param string                               $method
     * @param string|UriInterface                  $url
     * @param array                                $headers
     * @param resource|string|StreamInterface|null $body
     *
     * @return ResponseInterface
     *
     * @throws BokbasenApiClientException
     */
    public function request(string $method, $url, array $headers = [], $body = null): ResponseInterface
    {
        try {
            return $this->getHttpClient()->sendRequest(
                $this->getMessageFactory()->createRequest($method, $url, $headers, $body)
            );
        } catch (\Http\Client\Exception | \Exception $e) {
            throw new BokbasenApiClientException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function getHttpClient(HttpClient $httpClient = null): HttpClient
    {
        if (!$this->httpClient) {
            $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        }

        return $this->httpClient;
    }

    protected function getMessageFactory(MessageFactory $messageFactory = null): MessageFactory
    {
        if (!$this->messageFactory) {
            $this->messageFactory = $messageFactory ?: MessageFactoryDiscovery::find();
        }

        return $this->messageFactory;
    }
}
