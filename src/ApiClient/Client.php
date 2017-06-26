<?php
namespace Bokbasen\ApiClient;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Client\HttpClient;
use Psr\Http\Message\ResponseInterface;
use Bokbasen\Auth\Login;
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
     *
     * @var \Http\Client\HttpClient
     */
    protected $httpClient;

    /**
     *
     * @var \Http\Discovery\MessageFactory
     */
    protected $messageFactory;

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     *
     * @var \Bokbasen\Auth\Login
     */
    protected $login;

    /**
     *
     * @var string
     */
    protected $baseUrl;

    /**
     *
     * @param Login $login            
     * @param string $baseUrl            
     * @param LoggerInterface $logger            
     * @param HttpClient $httpClient            
     */
    public function __construct(Login $login, $baseUrl, LoggerInterface $logger = null, HttpClient $httpClient = null)
    {
        $this->login = $login;
        $this->baseUrl = $baseUrl;
        $this->setLogger($logger);
        $this->setHttpClient($httpClient);
        
        if (empty($this->baseUrl) || strpos($this->baseUrl, 'http') !== 0) {
            throw new BokbasenApiClientException('Base URL invalid or empty');
        }
    }

    /**
     * Execute POST request
     *
     * @param string $relativePath            
     * @param string $encodedData            
     * @param string $contentType            
     * @param array $additionalHeaders            
     * @param bool $authenticate            
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function post($relativePath, $encodedData, $contentType, array $additionalHeaders = [], $authenticate = true)
    {
        if (! empty($contentType)) {
            $additionalHeaders['Content-Type'] = $contentType;
        }
        
        return $this->executeHttpRequest(HttpRequestOptions::HTTP_METHOD_POST, $additionalHeaders, $encodedData, $this->buildUrl($relativePath), $authenticate);
    }

    /**
     * Execute POST request
     *
     * @param string $relativePath            
     * @param string $encodedData            
     * @param string $contentType            
     * @param array $additionalHeaders            
     * @param bool $authenticate            
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function put($relativePath, $encodedData, $contentType, array $additionalHeaders = [], $authenticate = true)
    {
        if (! empty($contentType)) {
            $additionalHeaders['Content-Type'] = $contentType;
        }
        
        return $this->executeHttpRequest(HttpRequestOptions::HTTP_METHOD_PUT, $additionalHeaders, $encodedData, $this->buildUrl($relativePath));
    }

    /**
     * Special endpoint for posting json, sets correct content type header and encodes data as json
     *
     * @param string $relativePath            
     * @param \stdClass|array $data            
     * @param array $additionalHeaders            
     * @param bool $authenticate            
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function postJson($relativePath, $data, array $additionalHeaders = [], $authenticate = true)
    {
        if (! is_array($data) || ! $data instanceof \stdClass) {
            throw new BokbasenApiClientException('Data must be array or stdClass');
        }
        $data = json_encode($data);
        
        if ($data === false) {
            throw new BokbasenApiClientException('Not able to convert data to json');
        }
        
        return $this->post($relativePath, $data, HttpRequestOptions::CONTENT_TYPE_JSON);
    }

    /**
     *
     * @param string $relativePath            
     * @param string $data            
     * @param string $contentType            
     * @param bool $authenticate            
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function get($relativePath, $accept = null, array $additionalHeaders = [], $authenticate = true)
    {
        if (! empty($accept)) {
            $additionalHeaders['Accept'] = $accept;
        }
        
        return $this->executeHttpRequest(HttpRequestOptions::HTTP_METHOD_GET, $additionalHeaders, null, $this->buildUrl($relativePath));
    }

    /**
     *
     * @param string $method            
     * @param string $contentType            
     * @param string $encodedData            
     * @param string $completeUrl            
     * @param bool $authenticate            
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function executeHttpRequest($method, array $additionalHeaders, $encodedData, $completeUrl, $authenticate = true)
    {
        if ($authenticate) {
            $headers = $this->makeHeadersArray($additionalHeaders);
        } else {
            $headers = $additionalHeaders;
        }
        
        if (! is_null($this->logger)) {
            $this->logger->debug(sprintf('Executing HTTP %s request to %s with data %s ', $method, $completeUrl, $encodedData));
        }
        
        $request = $this->getMessageFactory()->createRequest($method, $completeUrl, $headers, $encodedData);
        
        return $this->httpClient->sendRequest($request);
    }

    /**
     * Set HTTP client, if none is given autodetection is attempted
     *
     * @param HttpClient $httpClient            
     */
    public function setHttpClient(HttpClient $httpClient = null)
    {
        if (is_null($httpClient)) {
            $this->httpClient = HttpClientDiscovery::find();
            if (! is_null($this->logger)) {
                $this->logger->debug('HttpClientDiscovery::find() used to find HTTP client in ' . __CLASS__);
            }
        } else {
            $this->httpClient = $httpClient;
        }
    }

    /**
     *
     * @param LoggerInterface $logger            
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Build URL joining path with base URL
     *
     * @param string $relativePath            
     * @return string
     */
    protected function buildUrl($relativePath)
    {
        return $this->baseUrl . $relativePath;
    }

    /**
     * Create a message factory
     *
     * @return \Http\Discovery\MessageFactory
     */
    protected function getMessageFactory()
    {
        if (is_null($this->messageFactory)) {
            $this->messageFactory = MessageFactoryDiscovery::find();
        }
        
        return $this->messageFactory;
    }

    /**
     *
     * @param array $additionalHeaders            
     */
    protected function makeHeadersArray(array $additionalHeaders = [])
    {
        return array_merge($this->login->getAuthHeadersAsArray(), $additionalHeaders);
    }

    /**
     * Check if the auth client should attempt reauthetication based on response.
     * Will only run reauth once.
     *
     * @param ResponseInterface $response            
     * @return boolean
     */
    protected function needReAuthentication(ResponseInterface $response)
    {
        if ($response->getStatusCode() == 401 && ! $this->login->isReAuthAttempted()) {
            $this->login->reAuthenticate();
            return true;
        } else {
            return false;
        }
    }
}