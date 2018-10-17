<?php

namespace Bokbasen\ApiClient;

use Get;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Psr\Http\Message\ResponseInterface;
use Bokbasen\Auth\Login;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Bokbasen\ApiClient\Exceptions\BokbasenApiClientException;

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * Generic HTTP client for use against Bokbasen APIs.
 *
 * @license https://opensource.org/licenses/MIT
 */
class Client
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Login
     */
    protected $login;

    /**
     * @var Caller
     */
    protected $caller;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @param Login  $login
     * @param string $baseUrl
     */
    public function __construct(Login $login, string $baseUrl)
    {
        $this->login = $login;
        $this->baseUrl = $baseUrl;
    }

    protected function getCaller(): Caller
    {
        if (!$this->caller) {
            $this->caller = new Caller();
        }

        return $this->caller;
    }

    /**
     * @throws BokbasenApiClientException
     */
    protected function call(string $method, string $path, $body = null, ?array $headers = [], bool $authenticate = true): ResponseInterface
    {
        $headers = $authenticate ? $this->addAuthenticationHeaders($headers) : $headers;
        $url = $this->prependBaseUrl($path);

        if ($this->logger) {
            $this->logger->debug(sprintf('Executing HTTP %s request to %s with data %s.', $method, $url, $body));
        }

        return $this->getCaller()->request($method, $url, $headers, $body);
    }

    /**
     * Execute POST request
     *
     * @param string                               $path
     * @param resource|string|StreamInterface|null $body
     * @param array                                $headers
     * @param bool                                 $authenticate
     *
     * @return ResponseInterface
     *
     * @throws BokbasenApiClientException
     */
    public function post(string $path, $body, ?array $headers = [], bool $authenticate = true): ResponseInterface
    {
        return $this->call(
            HttpRequestOptions::HTTP_METHOD_POST,
            $path,
            $body,
            $headers,
            $authenticate
        );
    }

    /**
     * Execute PUT request
     *
     * @param string                               $path
     * @param resource|string|StreamInterface|null $body
     * @param array                                $headers
     * @param bool                                 $authenticate
     *
     * @return ResponseInterface
     *
     * @throws BokbasenApiClientException
     */
    public function put(string $path, $body, ?array $headers = [], bool $authenticate = true): ResponseInterface
    {
        return $this->call(
            HttpRequestOptions::HTTP_METHOD_PUT,
            $path,
            $body,
            $headers,
            $authenticate
        );
    }

    /**
     * Execute GET request
     *
     * @param string                               $path
     * @param resource|string|StreamInterface|null $body
     * @param array                                $headers
     * @param bool                                 $authenticate
     *
     * @return ResponseInterface
     *
     * @throws BokbasenApiClientException
     */
    public function get(string $path, array $headers = [], $authenticate = true): ResponseInterface
    {
        return $this->call(
            HttpRequestOptions::HTTP_METHOD_GET,
            $path,
            null,
            $headers,
            $authenticate
        );
    }

    /**
     * Execute PATCH request
     *
     * @param string                               $path
     * @param resource|string|StreamInterface|null $body
     * @param array                                $headers
     * @param bool                                 $authenticate
     *
     * @return ResponseInterface
     *
     * @throws BokbasenApiClientException
     */
    public function patch(string $path, $body, ?array $headers = [], bool $authenticate = true): ResponseInterface
    {
        return $this->call(
            HttpRequestOptions::HTTP_METHOD_PATCH,
            $path,
            $body,
            $headers,
            $authenticate
        );
    }

    /**
     * Special endpoint for posting json, sets correct content type header and encodes data as json
     *
     * @param string          $path
     * @param array|\stdClass $body
     *
     * @return ResponseInterface
     *
     * @throws BokbasenApiClientException
     */
    public function postJson(string $path, $body): ResponseInterface
    {
        if (!is_array($body) && !$body instanceof \stdClass) {
            throw new BokbasenApiClientException('Data must be array or stdClass');
        }

        $body = json_encode($body);

        if ($body === false) {
            throw new BokbasenApiClientException('Not able to convert data to json');
        }

        return $this->post(
            $path,
            $body,
            [
                'Content-Type' => HttpRequestOptions::CONTENT_TYPE_JSON,
            ]
        );
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger = null): void
    {
        $this->logger = $logger;
    }

    protected function prependBaseUrl(string $path): string
    {
        return sprintf('%s%s', $this->baseUrl, $path);
    }

    protected function addAuthenticationHeaders(array $existingHeaders = []): array
    {
        return array_merge($this->login->getAuthHeadersAsArray(), $existingHeaders);
    }
}