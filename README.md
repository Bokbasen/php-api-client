# php-api-client
Generic API client for use against all Bokbasen APIs where no spesific SDK is implemented that require authetication. 

The HTTP client is simple in use and you must implement API spesific functionality yourself. But it provides a standard interface to do request against Bokbasen APIs and allow you to use the Login SDK handling any complexity related to authentication.

```php
//First create a Login object [see php-sdk-auth](https://github.com/Bokbasen/php-sdk-auth)
use Bokbasen\Auth\Login;
try{
	$login = new Login('my_username', 'my_password');
} catch(\Exception $e){
	//error handling
}
```

```php
use Bokbasen\ApiClient;
use Bokbasen\ApiClient\HttpRequestOptions;
try{
	//pass the base URL of the API you are interacting with. You can also pass a logger and a custom http client. Any request made through the API returns an instance of \Psr\Http\Message\ResponseInterface. All of these API calls will include the necessary authentication headers.
	$client = new Client($login,'https://loan.api.boknett.no');
	
	//Execute get request, it is recommended to explicitly set accept parameter
	$response = $client->get('/budget', HttpRequestOptions::CONTENT_TYPE_JSON);

	//Execute POST request with json data
	$data = ['parameter' => 1];
	$response = $client->postJson('/budget',$data);
	
	//Execute POST request 
	$data = '<test>3</test>'
	$response = $client->post('/xmlReceiver',$data,HttpRequestOptions::CONTENT_TYPE_XML);	
	//Execute PUT request
	$data = '<test>3</test>'
	$response = $client->put('/xmlReceiver',$data,HttpRequestOptions::CONTENT_TYPE_XML);
	
	
	
} catch(\Exception $e){
	//error handling
}
```




