<?php


namespace App\Models;


use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use \Nmi\Authjwt\Models\User;

class UserValidator
{
    /** @var User $_user */
    private static $_user;

    /**
     * @return User
     */
    private static function user(): User
    {
        if(!static::$_user) {
            static::$_user = Auth::user();
        }

        return static::$_user;
    }

    public static function validateId($id = null)
    {
        // for manager, any id is allowed
        if(static::user()->hasRole('manager')) {
            return $id;
        }

        // for less then a manager only "mine" id is allowed
        if(!\in_array($id, ['me','mine']) || !$customer_id = static::user()->getMetaAttribute('customer_id')) {
            throw new UnauthorizedException('Unauthorized user');
            //abort(Response::HTTP_UNAUTHORIZED, 'Unauthorized user');
        }

        return $customer_id;
    }

    public static function validateBrand($brand): void
    {
        if(!static::user()->hasBrand($brand)) {
            throw new UnauthorizedException('Unauthorized user');
        }
    }
}