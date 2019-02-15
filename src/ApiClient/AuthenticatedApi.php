<?php declare(strict_types=1);

namespace Bokbasen\ApiClient;

use Bokbasen\ApiClient\Exceptions\MissingParameterException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AuthenticatedApi
{
    /**
     * @var string
     */
    private $loginEndpoint;

    /**
     * @var string
     */
    private $loginUsername;

    /**
     * @var string
     */
    private $loginPassword;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var CacheItemPoolInterface
     */
    protected $cache;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $endpoint;

    public function get(string $path, array $headers = [], bool $authenticate = true): ResponseInterface
    {
        return $this->getClient()->get($path, $headers, $authenticate);
    }

    public function post(string $path, $body, array $headers = [], $authenticate = true): ResponseInterface
    {
        return $this->getClient()->post($path, $body, $headers, $authenticate);
    }

    public function patch(string $path, $body, array $headers = [], bool $authenticate = true): ResponseInterface
    {
        return $this->getClient()->patch($path, $body, $headers, $authenticate);
    }

    public function put(string $path, $body, array $headers = [], $authenticate = true): ResponseInterface
    {
        return $this->getClient()->put($path, $body, $headers, $authenticate);
    }

    protected function getClient(): Client
    {
        $this->isReady();

        if (!$this->client) {
            $login = new Login($this->loginUsername, $this->password, $this->loginEndpoint, $this->cache, $this->logger);
            $this->client = new Client($login, $this->orderEndpoint);
        }

        return $this->client;
    }

    public function setEndpoint(string $endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    public function setCredentials(string $username, string $password, string $endpoint): void
    {
        $this->username = $username;
        $this->password = $password;
        $this->loginEndpoint = sprintf('%s/v1/tickets', $endpoint);
    }

    public function setLogger(LoggerInterface $logger = null): void
    {
        $this->logger = $logger;
    }

    public function setCache(CacheItemPoolInterface $cacheItemPool = null): void
    {
        $this->cache = $cacheItemPool;
    }

    private function isReady(): bool
    {
        $params = [
            'loginEndpoint',
            'loginUsername',
            'loginPassword',
            'endpoint',
        ];

        foreach ($params as $param) {
            if (!$this->$param) {
                throw new MissingParameterException($param);
            }
        }

        return true;
    }
}
