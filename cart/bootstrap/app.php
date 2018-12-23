<?php

require_once __DIR__ . '/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__ . '/../')
);

$app->withFacades();

$app->register(Jenssegers\Mongodb\MongodbServiceProvider::class);
$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);


/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/
$app->middleware([
                     \Nmi\Authjwt\Middleware\Authenticate::class
                 ]);
/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(\App\Providers\AppServiceProvider::class);
$app->register(\App\Providers\EventServiceProvider::class);
$app->register(\GreenSmoke\HealthChecks\ServiceProvider\HealthCheckServiceProvider::class);
$app->register(\NMI\LumenLogger\ServiceProvider\LumenLoggerServiceProvider::class);
$app->register(\Nmi\Authjwt\AuthJwtProvider::class);
$app->register(\App\Providers\RouteBindingProvider::class);
$app->register(\Illuminate\Redis\RedisServiceProvider::class);
$app->register(\Prwnr\Streamer\StreamerProvider::class);

// Package uses Laravel discovery, so we have to define alias manually
$app->alias('Streamer', \Prwnr\Streamer\Facades\Streamer::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group(['namespace' => 'App\Http\Controllers'], function ($app) {
    require __DIR__ . '/../routes/web.php';
});

$app->configure('app');
$app->configure('cache');
$app->configure('database');
$app->configure('services');
$app->configure('logging');
$app->configure('health');
$app->configure('oauth');
$app->configure('behat');
$app->configure('streamer');

return $app;
