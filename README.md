# Laravel Request Logger

[![Latest Version on Packagist](https://img.shields.io/packagist/v/always-open/laravel-request-logger.svg?style=flat-square)](https://packagist.org/packages/always-open/laravel-request-logger)
[![Build Status](https://img.shields.io/github/workflow/status/always-open/laravel-request-logger/run-tests/main)](https://github.com/always-open/laravel-request-logger/actions?query=workflow%3Arun-tests)
[![GitHub Workflow Status (branch)](https://img.shields.io/github/workflow/status/always-open/laravel-request-logger/PHPStan/main?label=PHPStan)](https://github.com/always-open/laravel-request-logger/actions?query=workflow%3APHPStan)
[![Packagist Downloads](https://img.shields.io/packagist/dt/always-open/laravel-request-logger)](https://packagist.org/packages/always-open/laravel-request-logger)

[![Maintainability](https://api.codeclimate.com/v1/badges/50523859ead2baf5d6af/maintainability)](https://codeclimate.com/github/always-open/laravel-request-logger/maintainability)

When making HTTP requests to external APIs it is valuable to track each request and its response. This insight can help 
you find issues, track usage, and reuse responses for testing/development.

## Installation

You can install the package via composer:

```bash
composer require always-open/laravel-request-logger
```

### Breaking change
If you are upgrading to 3.x or newer, the following steps must be taken as new fields have been added to the logging tables.
- Create a migration for each logging tables with the following
```php
// The headers that were part of the request
$table->json('request_headers')
    ->nullable();
// The headers that were part of the response
$table->json('response_headers')
    ->nullable();
```
Run this migration prior to upgrading the package.

## Configuration

``` php
php artisan vendor:publish --provider="\AlwaysOpen\RequestLogger\RequestLoggerServiceProvider"
```

Running the above command will publish the config file.

## Usage

### Creation
To add logs to your system you must first create the migration and model for the appropriate log. This is done by using 
the packages `request-logger:make-table` command.

The command needs the name of the item to be tracked and it will be used for naming the model and table.

#### Example
```shell
php artisan request-logger:make-table facebook
```
This will create a model `\App\Models\FacebookRequestLog` and a migration to create the table `facebook_request_logs`

### Implementation
Then you can use that model to create logs of your requests where you can make the API calls.

#### Example

##### Guzzle
```php
function makeFacebookApiCall(array $body, Client $facebook_client)
{
    $request_headers = [
        'api-key' => $config->apiKey,
        'Content-Type' => 'application/json',
    ];

    $versioned_path = self::buildVersionedUrlPath($path);

    $encoded_body = json_encode($body, JSON_UNESCAPED_SLASHES);

    $request = new Request(
        'GET',
        '/v1/users',
        $request_headers,
        $encoded_body,
    );
    
    $request_log = FacebookRequestLog::makeFromGuzzle($request);
    
    $response = $client->send($request);
    
    $request_log->response_code = $response->getStatusCode();
    $request_log->response = json_decode((string)$response->getBody(), true);
    $request_log->save();
}
```

Instead of manually setting the response data you can instead leverage the `updateFromResponse` method:
```php
function makeFacebookApiCall(array $body, Client $facebook_client)
{
    $request_headers = [
        'api-key' => $config->apiKey,
        'Content-Type' => 'application/json',
    ];

    $versioned_path = self::buildVersionedUrlPath($path);

    $encoded_body = json_encode($body, JSON_UNESCAPED_SLASHES);

    $request = new Request(
        'GET',
        '/v1/users',
        $request_headers,
        $encoded_body,
    );
    
    $request_log = FacebookRequestLog::makeFromGuzzle($request);
    
    $response = $client->send($request);
    $request_log->updateFromResponse($response);
}
```


You can also manually set each property and then save the log instance.

### Testing

``` bash
composer test
```

### Using Docker
All assets are set up under the docker-compose.yml file. The first time you run the docker image you must build it with
the following command:
```bash
./docker.sh -b -s
```

Then you can bring it up in the background using:
```bash
./docker.sh -d
```

From there you can run the tests within an isolated environment

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email @qschmick instead of using the issue tracker.

## Credits

- [Quentin Schmick](https://github.com/qschmick)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
