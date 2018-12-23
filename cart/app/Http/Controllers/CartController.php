<?php
/**
 * Created by PhpStorm.
 * User: yoseff
 * Date: 10/29/2018
 * Time: 1:35 PM
 */

namespace App\Http\Controllers;


use App\Models\Cart;
use App\Models\UserValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends BaseController
{
    /**
     * GET the list of Carts, or only one
     *
     * @param Request $request
     * @param Cart    $cart Do NOT type hint in the function
     *
     * @return JsonResponse
     */
    public function index(Request $request, $cart = null): JsonResponse
    {
        $resp = [];
        if ($cart) {
            $resp = $cart->toArray();
        } else {
            $input     = $request->all();
            if(!empty($input['brand_id'])) {
                UserValidator::validateBrand($input['brand_id']);
            }
            $q         = $this->applyFilters($input, Cart::query());
            $page      = $request->input('page', 1);
            $columns   = $request->input('columns', '*');
            $paginator = $q->latest()->paginate(100, [$columns], 'page', $page);

            /** @var Cart $obj */
            foreach ($paginator as $obj) {
                $resp[] = $obj->toArray();
            }
        }

        return $this->success($resp);
    }

    /**
     * Create new cart with products for current user
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $input = $this->validate($request, Cart::getRules(), static::$messages);
        $brandId = $input['brand_id'];
        UserValidator::validateBrand($brandId);
        $customerId = UserValidator::validateId($input['customer_id']);

        // try to get an existing cart, or create a new one

        /** @var Cart $cart  */
        $cart = Cart::query()
                    ->where('customer_id', '=', $customerId)
                    ->where('brand_id', '=', $brandId)
                    ->first();
        if (!$cart) {
            $cart = new Cart(['brand_id' => $brandId, 'customer_id' => $customerId]);
            if (!$cart->save()) {
                return $this->error('Failed to create new cart.');
            }
        }

        if(!$this->addCartProducts($request, $cart)) {
            return $this->error('Failed to add products.');
        }

        return $this->created($cart->toArray());
    }

    /**
     * @param Cart $cart
     *
     * @return JsonResponse|\Illuminate\Http\Response
     */
    public function delete(Cart $cart)
    {
        return $cart->delete() ? $this->deleted() : $this->error();
    }
}