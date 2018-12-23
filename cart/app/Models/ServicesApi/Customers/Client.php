<?php

namespace App\Models\ServicesApi\Customers;


use App\Models\ServicesApi\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

class Client extends ApiClient
{
    public function __construct(array $config = [])
    {
        $config['base_uri'] = config('services.customers.url');
        parent::__construct($config);
    }

    /**
     * @param $customer_id
     *
     * @return PromiseInterface|ResponseInterface
     */
    public function getCustomer($customer_id)
    {
        return $this->request('get',"/customers/$customer_id");
    }

    public function getArrayResponse(ResponseInterface $r): array
    {
        $resp = parent::getArrayResponse($r);

        return array_get($resp, 'data', []);
    }
}