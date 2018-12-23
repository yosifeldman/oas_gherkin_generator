<?php

namespace App\Providers;


use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\UserValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use mmghv\LumenRouteBinding\RouteBindingServiceProvider;
use Nmi\Authjwt\Models\User;

class RouteBindingProvider extends RouteBindingServiceProvider
{
    public function boot(): void
    {
        $binder = $this->binder;
        $binder->bind('cart', function ($cartId) {
            return $this->findCart($cartId);
        });

        $binder->bind('coupon', Coupon::class);

        $binder->compositeBind(['cart', 'product'], function ($cartId, $prodId) {
            $cart = $this->findCart($cartId);
            if (!$product = $cart->products()->find((string) $prodId)) {
                $this->notFound(Product::class);
            }

            return [$cart, $product];
        });
    }

    public function findCart($cartId): Cart
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Cart $cart */
        if ($user->hasRole('manager')) {
            $cart = Cart::query()->findOrFail((string) $cartId);
        } elseif ($customerId = UserValidator::validateId($cartId)) {
            $cart = Cart::query()
                        ->where('customer_id', '=', $customerId)
                        ->where('brand_id', '=', $user->brand()->id)
                        ->first();
        }
        if (!$cart) {
            $this->notFound(Cart::class);
        }

        return $cart;
    }

    public function notFound($modelName = ''): void
    {
        throw (new ModelNotFoundException())->setModel($modelName);
    }
}