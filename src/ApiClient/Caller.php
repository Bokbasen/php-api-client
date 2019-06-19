<?php

namespace Bokbasen\ApiClient;

use Bokbasen\ApiClient\Exceptions\BokbasenApiClientException;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Discovery\StreamFactoryDiscovery;
use Http\Message\MessageFactory;
use Http\Message\StreamFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
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
     * @var StreamFactory
     */
    private $streamFactory;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    public function __construct(
        ClientInterface $httpClient = null,
        RequestFactoryInterface $requestFactory = null,
        StreamInterface $streamFactory = null
    ) {
        $this->httpClient = $httpClient ?: Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?: Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?: Psr17FactoryDiscovery::findStreamFactory();
    }

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
            $request = $this->requestFactory->createRequest($method, $url);

            if (!empty($headers)) {
                foreach ($headers as $name => $value) {
                    $request = $request->withHeader($name, $value);
                }
            }

            if ($body !== null) {
                $request = $request->withBody(
                    $this->streamFactory->createStream($body)
                );
            }

            return $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface | \Exception $e) {
            throw new BokbasenApiClientException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function setHttpClient(ClientInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }
}
