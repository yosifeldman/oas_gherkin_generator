<?php

namespace App\Models\ServicesApi\Taxes;


use App\Models\ServicesApi\ApiClient;
use GuzzleHttp\RequestOptions;


/**
 * Class Client
 * @package App\Models\ServicesApi\Taxes
 *
 */
class Client extends ApiClient
{
    public function __construct(array $config = [])
    {
        $config['base_uri'] = config('services.taxes.url');
        parent::__construct($config);
    }

    public function getTaxesForAddress($address)
    {
        $uri = '/taxes';
        $req = [
            RequestOptions::JSON => [
                'country_id'         => array_get($address, 'country', 'US'),
                'region_code'        => $address['region'],
                'postcode'           => $address['postcode'],
                'city'               => $address['city'],
                'customer_tax_class' => 'retail_customer',
                'product_tax_class'  => 'taxable_goods'
            ]
        ];

        return $this->request('post', $uri, $req);
    }
}