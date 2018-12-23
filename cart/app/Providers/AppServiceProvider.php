<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Ramsey\Uuid\Uuid;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('money', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^\d*([\.,]\d*)?$/', $value);
        });
        Validator::extend('uuid', function ($attribute, $value, $parameters, $validator) {
            return Uuid::isValid($value);
        });
        Validator::extend('percentage', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^0[\.,]\d+$/', $value);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
