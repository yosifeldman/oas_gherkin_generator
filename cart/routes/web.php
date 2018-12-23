<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->group(['prefix' => 'carts'], function () use ($app) {

    // All carts
    $app->get('', 'CartController@index');
    $app->post('', 'CartController@create');

    // Actions on specific cart
    $app->group(['prefix' => '{cart}'], function () use ($app) {

        // Read/Delete
        $app->get('', 'CartController@index');
        $app->delete('', 'CartController@delete');

        // Products
        $app->group(['prefix' => 'items'], function () use ($app) {
            $app->post('', 'ProductController@add');
            $app->put('{product}', 'ProductController@update');
            $app->delete('', 'ProductController@delete');
            $app->delete('{product}', 'ProductController@delete');
        });

        // Coupon
        $app->group(['prefix' => 'coupon'], function () use ($app) {
            $app->post('', 'CouponController@addToCart');
            $app->delete('', 'CouponController@deleteFromCart');
        });

        // Shipping
        $app->group(['prefix' => 'shipping'], function () use ($app) {
            $app->post('', 'ShippingController@addShippingMethod');
        });

        // Checkout
        $app->group(['prefix' => 'checkout'], function () use ($app) {
            $app->post('', 'CheckoutController@placeOrder');
        });
    });
});

// Shipping
$app->get('shipping', 'ShippingController@index');

// Coupons CRD
$app->group(['prefix' => 'coupons'], function () use ($app) {
    $app->get('', 'CouponController@index');
    $app->get('{coupon}', 'CouponController@index');
    $app->post('', 'CouponController@create');
    $app->delete('{coupon}', 'CouponController@delete');
});
