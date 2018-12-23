<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Streamer listen timeout
    |--------------------------------------------------------------------------
    |
    | Milliseconds after which Streamer listen block should timeout
    | Setting 0 never timeouts.
    |
    */
    'listen_timeout' => 0,

    /*
    |--------------------------------------------------------------------------
    | Streamer event domain
    |--------------------------------------------------------------------------
    |
    | Domain name which streamer should use when
    | building message with JSON schema
    |
    */
    'domain' => env('APP_NAME', ''),

    /*
    |--------------------------------------------------------------------------
    | Application events
    |--------------------------------------------------------------------------
    |
    | Events classes that should be invoked with Streamer listen command
    | based on streamer_event_name => [local_events] pairs
    |
    */
    'listen_and_fire' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom config
    |--------------------------------------------------------------------------
    |
    | List of emitted events that are allowed to be fired.
    | Events that are not in this list won't be emitted to Redis Stream
    |
    */
    'available_events' => [
        'order.placed',
        'cart.created', 'cart.updated', 'cart.deleted',
        'cart.product.created', 'cart.product.updated', 'cart.product.deleted',
        'cart.coupon.created', 'cart.coupon.deleted',
        'cart.shipping.created',
        'coupon.created', 'coupon.deleted'
    ]
];
