<?php
/**
 * Created by PhpStorm.
 * User: yoseff
 * Date: 12/4/2018
 * Time: 5:51 PM
 */

namespace App\Http\Controllers;


use App\Models\Cart;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends BaseController
{
    /**
     * GET the list of Coupons, or only one
     *
     * @param Request $request
     * @param Cart    $coupon Do NOT type hint in the function
     *
     * @return JsonResponse
     */
    public function index(Request $request, $coupon = null): JsonResponse
    {
        $resp = [];
        if ($coupon) {
            $resp = $coupon->toArray();
        } else {
            $input     = $request->all();
            $q         = $this->applyFilters($input, Coupon::query());
            $page      = $request->input('page', 1);
            $columns   = $request->input('columns', '*');
            $paginator = $q->latest()->paginate(100, [$columns], 'page', $page);

            /** @var Coupon $obj */
            foreach ($paginator as $obj) {
                $resp[] = $obj->toArray();
            }
        }

        return $this->success($resp);
    }

    public function create(Request $request): JsonResponse
    {
        $input = $this->validate($request, Coupon::getRules(), static::$messages);

        $coupon = new Coupon($input);

        return $coupon->save() ? $this->created($coupon->toArray()) : $this->error();
    }

    public function delete(Coupon $coupon)
    {
        return $coupon->delete() ? $this->deleted() : $this->error();
    }

    public function addToCart(Request $request, Cart $cart): JsonResponse
    {
        $input = $this->validate($request, [
            'coupon_code' => 'required|alpha_dash'
        ], static::$messages);

        $coupon = Coupon::query()->findOrFail($input['coupon_code']);
        $cart->discount = $coupon->toArray();

        return $cart->save() ? $this->success($cart->getSummary()) : $this->error();
    }

    public function deleteFromCart(Cart $cart)
    {
        $cart->discount = null;

        return $cart->save() ? $this->deleted() : $this->error();
    }
}