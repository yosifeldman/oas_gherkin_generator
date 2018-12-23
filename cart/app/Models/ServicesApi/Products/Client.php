<?php


namespace App\Models\ServicesApi\Products;


use App\Models\ServicesApi\ApiClient;
use GuzzleHttp\RequestOptions;
use Prophecy\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

class Client extends ApiClient
{
    public function __construct(array $config = [])
    {
        $config['base_uri'] = config('services.products.url');
        parent::__construct($config);
    }

    /**
     * @param      $brand
     * @param null $sku
     *
     * @return PromiseInterface|ResponseInterface
     */
    public function getBrandProducts($brand, $sku = null)
    {
        $req = [
            'headers' => [
                'x-tenant-id' => $brand
            ]
        ];

        if($sku) {
            $req[RequestOptions::QUERY] = ['sku' => $sku];
        }

        return $this->request('get', '/products', $req);
    }
}