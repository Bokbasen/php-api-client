# php-api-client

[![Build Status](https://scrutinizer-ci.com/g/Bokbasen/php-api-client/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Bokbasen/php-api-client/build-status/master) [![Code Coverage](https://scrutinizer-ci.com/g/Bokbasen/php-api-client/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Bokbasen/php-api-client/?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Bokbasen/php-api-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Bokbasen/php-api-client/?branch=master)

[Click here if you're looking for documentation to version 1.*](https://github.com/Bokbasen/php-api-client/tree/v1.0.1)

Generic API client for use against all Bokbasen APIs where no spesific SDK is implemented that require authetication. 

The HTTP client is simple in use and you must implement API spesific functionality yourself. But it provides a standard interface to do request against Bokbasen APIs and allow you to use the Login SDK handling any complexity related to authentication.

First create a Login object [see php-sdk-auth for details](https://github.com/Bokbasen/php-sdk-auth)

```php
use Bokbasen\Auth\Login;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

try {
    // This example is using a file cache for the TGT, you can replace this with any PSR-6 compatible cache. Always using caching in production to avoid performance penalty of creating and deleting tokens
    $cache = new FilesystemAdapter();
    $login = new Login('username', 'password', Login::URL_PROD, $cache);
} catch(\Throwable $e) {
    // error handling
}
```

```php
use Bokbasen\ApiClient\Client;
use Bokbasen\ApiClient\HttpRequestOptions;
use Bokbasen\ApiClient\Exceptions\BokbasenApiClientException;

try {
    // Pass the base URL of the API you are interacting with. You can also pass a logger and a custom http client. Any request made through the API returns an instance of \Psr\Http\Message\ResponseInterface. All of these API calls will include the necessary authentication headers.
    $client = new Client($login, 'https://loan.api.boknett.no');
    
    // Execute get request, it is recommended to explicitly set accept parameter
    $headers = ['Accept' => HttpRequestOptions::CONTENT_TYPE_JSON];
    $response = $client->get('/path', $headers, $authenticate);
    
    // Execute POST request with json data
    $response = $client->postJson('/path', $body, $headers, $authenticate);
    
    // Execute POST request 
    $response = $client->post('/path', $body, $headers, $authenticate);
    
    // Execute PUT request
    $response = $client->put('/path', $body, $headers, $authenticate);
    
    // Execute PATCH request
    $response = $client->patch('/path', $body, $headers, $authenticate);
} catch(BokbasenApiClientException $e){
    //error handling
}
```
