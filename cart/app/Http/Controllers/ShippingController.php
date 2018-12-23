<?php
/**
 * Created by PhpStorm.
 * User: yoseff
 * Date: 12/4/2018
 * Time: 5:54 PM
 */

namespace App\Http\Controllers;


use App\Models\Cart;
use App\Models\ShippingMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends BaseController
{
    public function index(): JsonResponse
    {
        return $this->success(ShippingMethod::get());
    }

    public function addShippingMethod(Request $request, Cart $cart): JsonResponse
    {
        $input = $this->validate($request, ['shipping_method' => 'required|string'], static::$messages);

        if($shipping = ShippingMethod::get($input['shipping_method'])) {
            $cart->shipping = $shipping;
        } else {
            $this->validationError('The requested shipping method is invalid.');
        }

        return $cart->save() ? $this->success($cart->getSummary()) : $this->error();
    }
}