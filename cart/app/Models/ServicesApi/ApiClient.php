<?php

namespace App\Models\ServicesApi;


use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Nmi\Authjwt\MicroserviceAuth;
use Psr\Http\Message\ResponseInterface;

abstract class ApiClient
{
    protected $headers = [], $async = false;

    /** @var  Client */
    protected $client;

    public function __construct(array $config = [])
    {
        $config['headers'] = app('nmi.logger')->getTransactionHeaders();
        $config['handler'] = MicroserviceAuth::JWTHandler();

        $this->client = new Client($config);
    }

    public function getArrayResponse(ResponseInterface $r): array
    {
        if ($r->getStatusCode() === Response::HTTP_OK) {
            $result = \GuzzleHttp\json_decode($r->getBody()->getContents(), true);
        }

        return $result ?? [];
    }

    public function async($async = true)
    {
        $this->async = $async;

        return $this;
    }

    public function request($method, $uri, $options = [])
    {
        $method .= $this->async ? 'Async' : '';
        return $this->client->$method($uri, $options);
    }
}