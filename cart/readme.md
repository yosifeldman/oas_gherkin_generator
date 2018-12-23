# Lumen PHP Framework

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://poser.pugx.org/laravel/lumen-framework/d/total.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://poser.pugx.org/laravel/lumen-framework/v/stable.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Unstable Version](https://poser.pugx.org/laravel/lumen-framework/v/unstable.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://poser.pugx.org/laravel/lumen-framework/license.svg)](https://packagist.org/packages/laravel/lumen-framework)

Laravel Lumen is a stunningly fast PHP micro-framework for building web applications with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Lumen attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as routing, database abstraction, queueing, and caching.

## Official Documentation

Documentation for the framework can be found on the [Lumen website](http://lumen.laravel.com/docs).

# Cart MicroService

### Responsibilities

This Micro Service is responsible for storing the users' shopping cart & for the Checkout process.

### Authentication

User authentication requires POST request to /authenticate URL. Authentication accepts JSON and normal POST form data.
Required parameters for user Authentication are: **username** and **password**.
Example:
```json
{"username":"magento","password":"secret" }
```
API in response will return JSON with encrypted token that should be used in every guarded request call.
Token is encrypted with openssl method for which .env file should include configuration of
password, encryption method and IV. See PHP documentation for available methods: http://php.net/manual/pl/function.openssl-get-cipher-methods.php. Check .env.example file to find how those variables should be named.

Since v1.0.7 token should be passed in request HTTP headers as **Authorization** header for each guarded URL.
Example:
```
Content-Type:application/json
Authorization:token_string_hash
```

### User authorization within API routes

There are two ways to check if current token is authorized to get response from called request.
The first one is whenever route is defined. If we want to protect our post request our route
declaration will be slightly different. Argument passed as second will be an array of defined
middleware and what it uses. Example:
```php
$app->post('/', ['middleware' => 'auth', 'uses' => 'FooController@bar']);
```

The second one is to specify our middleware inside of our controller's constructor.
This approach let us define what actions from inside the controller are being protected and what not.
Example:
```php
public function __construct()
{
    $this->middleware('auth', [
        'only' => [
            'foo', 'bar'
        ]
    ]);
}
```

### MicroService Logs

Lumen is configured to create a rotating daily log files for application which is configurable. You may write information to the logs using the Log facade:

```
\Log::info('hello message');
```
The log facade is configured to standardizes log records to include important information:
**date, IP, unique call ID, service name,log level, message and context, access url**.

> [%datetime%] [UID: %uid%, IP: %ip%] %channel%.%level_name%: %message% %context% [%extra.http_method% %extra.url%]



Define Micro Service name, Log File location and Max log files inside build.sh file (MICROSERVICE_NAME, LOG_FILE and MAX_LOG_FILES variables).

Correlation ID logging: the logger will auto-detect the `x-correlation-id` and `x-forwarded-by` headers of HTTP request, and add them to each log record as UID.

The logger provides the eight logging levels defined in RFC 5424: _emergency, alert, critical, error, warning, notice, info and debug_.

```
Log::emergency($error);
Log::alert($error);
Log::critical($error);
Log::error($error);
Log::warning($error);
Log::notice($error);
Log::info($error);
Log::debug($error);
```

By default the logger will write the logs with default channel name (MICROSERVICE_NAME), but can be changed if needed.

```
$logger = \Log::getFacadeRoot()->withName('reqres');
$logger->debug('Request recieved', [$requestData]);
```

Examples of how log record will look like:
```
[2017-12-05 12:37:55] [UID: 151247747475911, IP: 10.1.20.45] authentication.INFO: Created token for user after successful login. [] [POST /authenticate]
[2017-12-05 12:37:55] [UID: 151247747475911, IP: 10.1.20.45] authentication.INFO: Successful attempt of user login and token creation for current session. [] [POST /authenticate]
[2017-12-05 12:37:55] [UID: 151247747475911, IP: 10.1.20.45] reqres.DEBUG: Request recieved [{"username":"greensmoke_ver","password":"h%1sTfV-A4"}] [POST /authenticate]
[2017-12-05 12:37:55] [UID: 151247747475911, IP: 10.1.20.45] reqres.DEBUG: Response created [{"token":"RYPA9KikpMM+msJWhsMTcGIA7vyRdvfu71aaMpO2ayVSlZ34WpiIDb+O2zS8BKT0kG2xyYGAjJvX4wh4DjGRpu+zZFaRKgyCz9hlh67vMXI="}] [POST /authenticate]
```

### BDD - Behat testing

Lumen Template includes integration with Behat framework.
Base Behat configuration is located in behat.yml file. Most important part of this file is base_url variable that determines what base URL should be called when running tests.
By default this value is set to: http://127.0.0.1:8000.
base_path variable determines root project directory and is used to boot Lumen Application environment.

To use this integration your Behat context class have to extend `App\Models\BehatIntegration\LumenMicroService` class,
which boots Lumen App and provides set of HTTP methods like get, post, put, patch, delete. Your context can extend `MicroSerivceContext` instead
of `LumenMicroService` class, but if you do so, you will have to remove `MicroServiceContext` class from `behat.yml` configuration file.

URL used in request methods should NOT include base URL, just path, it will be combined automatically before making the call.
This integration allows you to create test user by calling `createTestUser($data)` method with username and password of your choice.

`App\Models\BehatIntegration` namespace also provides two traits called: `DatabaseTransactions` and `DatabaseMigrations`, that when used will provide methods that are using Behat hooks.
 * **DatabaseTransactions** - this trait can be used if there is a need of running Scenario encapsulated within database transaction. 
 It will start transaction before Scenario starts and will rollback transaction once Scenario ends. This trait should be used in your context class.
 * **DatabaseMigrations** - this trait should be used when your feature context will have to do operations on database.
 It will run migration for each of your scenarios with testing database settings and will seed it with data from seeders (be aware that this won't use existing database data at all). By default it will only migrate data (due to SQLite with memory storage use).
 Use this trait within your Context class (check `MicroServiceContext` and `AuthenticationContext` too see how it's used).

It is suggested to use only one trait in your context. For example, `DatabaseMigrations` trait will
 migrate **testing** database configured in `config/database.php` file. By default this database uses SQLite driver and is stored in memory. It will vanish 
 once Lumen instance will be unset when Scenario ends. Meaning, that there is no need to use database transactions, because data won't be
 stored in real database.
 
While using `DatabaseMigrations` trait, there is property called `migrationDatabaseName` which can be changed
to whatever database is in configuration file. However, once it will be changed to something different than **testing**, then rollback method will be called
when Scenario ends. This may have an impact on real database, because it will rollback all migrations and drop all tables, and they won't be populated back with previous data. 
Change this property with caution!

If any of those traits is used within your Context class, it should extend `LumenMicroService` class as well.
If your Context extends MicroServiceContext class, then `DatabaseMigrations` trait is already in use in that class.

`MicroServiceContext` - class contains `@Given /^I am authenticated as "([^"]*)"$/` step that performs action similar to user authentication and adds required Authorization header to HTTP request.
When Scenario requires user authentication, Context class associated with it should extend MicroServiceContext (which then should be commented out in behat.yml file).
