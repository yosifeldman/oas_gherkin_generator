<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */
    'example' => [
        'domain' => 'https://jsonplaceholder.typicode.com'
    ],
    'customers' => [
        'url' => env('CUSTOMERS_URL')
    ],
    'taxes' => [
        'url' => env('TAXES_URL')
    ],
    'products' => [
        'url' => env('PRODUCTS_URL')
    ]
];