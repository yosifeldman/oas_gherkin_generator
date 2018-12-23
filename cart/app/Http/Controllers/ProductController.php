<?php
/**
 * Created by PhpStorm.
 * User: yoseff
 * Date: 10/29/2018
 * Time: 2:28 PM
 */

namespace App\Http\Controllers;


use App\Models\Cart;
use App\Models\Product;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends BaseController
{
    public function add(Request $request, Cart $cart): JsonResponse
    {
        if(!$this->addCartProducts($request, $cart)) {
            return $this->error('Failed to add products.');
        }

        return $this->success($cart->toArray());
    }

    public function update(Request $request, Cart $cart, Product $product): JsonResponse
    {
        $input = $this->validate($request, Product::getRules(true), static::$messages);

        if($product->update($input) && $cart->products()->save($product)) {
            return $this->success($product->toArray());
        }

        return $this->error();
    }

    /**
     * @param Cart    $cart
     * @param Product $product
     * @return JsonResponse|Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function delete(Cart $cart, Product $product)
    {
        if ($product->getKey()) {
            $product->delete();
        } else {
            /** @var Product $prod */
            foreach($cart->products() as $prod) {
                $prod->delete();
            }
        }

        return $cart->save() ? $this->deleted() : $this->error();
    }
}