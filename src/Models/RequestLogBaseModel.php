<?php

namespace AlwaysOpen\RequestLogger\Models;

use AlwaysOpen\RequestLogger\Observers\RequestLogObserver;
use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;

/**
 * AlwaysOpen\RequestLogger\Models\RequestLogBaseModel
 *
 * @property string|null          $path
 * @property string|null          $params
 * @property string               $http_method
 * @property int|null             $response_code
 * @property array|string|null    $body
 * @property array|string|null    $request_headers
 * @property array|string|null    $response
 * @property array|string|null    $response_headers
 * @property string|null          $exception
 * @property \Carbon\Carbon|null  $occurred_at
 */
class RequestLogBaseModel extends Model
{
    protected $casts = [
        'occurred_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'body' => 'json',
        'request_headers' => 'json',
        'response' => 'json',
        'response_headers' => 'json',
    ];

    protected $guarded = [
        'created_at',
        'updated_at',
    ];

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected static function boot()
    {
        parent::boot();
        parent::observe(RequestLogObserver::class);
    }

    public static function make(array $props) : static
    {
        /** @phpstan-ignore-next-line */
        return new static($props + ['occurred_at' => now()]);
    }

    public static function makeFromGuzzle(Request $request) : static
    {
        /** @phpstan-ignore-next-line */
        $instance = new static();
        $instance->occurred_at = now();
        $instance->params = $request->getUri()->getQuery();
        $instance->path = $request->getUri()->getPath();
        $instance->http_method = $request->getMethod();
        $instance->body = $request->getBody()->getContents();
        $instance->request_headers = $request->getHeaders();

        return $instance;
    }

    public function updateFromResponse(ResponseInterface $response): self
    {
        $this->response = json_decode($response->getBody()->getContents(), true);
        $this->response_code = $response->getStatusCode();
        $this->response_headers = $response->getHeaders();

        $this->save();

        return $this;
    }
}
