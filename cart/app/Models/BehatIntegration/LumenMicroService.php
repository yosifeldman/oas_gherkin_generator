<?php

namespace App\Models\BehatIntegration;

use App\Models\BehatIntegration\Bootstrap\LumenClient;
use App\Models\User;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\Concerns\MakesHttpRequests;
use \Nmi\Authjwt\AuthRequestService;

/**
 * Class LumenMicroService
 * @package App\Models\BehatIntegration
 */
abstract class LumenMicroService
{

    use MakesHttpRequests {
        call as requestCall;
    }

    public $token;

    /**
     * @var mixed
     */
    public static $staticApp;

    /**
     * @var \Illuminate\Container\Container
     */
    public $app;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * Base URL used within context.
     * @var string
     */
    protected $baseUrl = '';

    /**
     * Base project path
     * @var string
     */
    protected $basePath = '';

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function beforeScenario(BeforeScenarioScope $scope): void
    {
        $this->baseUrl  = $scope->getSuite()->getSetting('base_url');
        $this->basePath = $scope->getSuite()->getSetting('base_path');
        $this->startLumen();
    }

    /**
     * @AfterScenario
     *
     * @param AfterScenarioScope $scope
     */
    public function afterScenario(AfterScenarioScope $scope): void
    {
        /**
         * Optional.
         * Check if DatabaseMigration trait is used.
         * If migrationDatabaseName property is not equal to 'testing' call rollback method on currently used database.
         */
        if (Helper::classUsesTrait($this, DatabaseMigrations::class) && $this->migrationDatabaseName !== 'testing') {
            $this->rollback();
        }

        $this->shutDownLumen();
    }

    /**
     * Call the given URI and return the Response.
     *
     * @param  string $method
     * @param  string $uri
     * @param  array  $parameters
     * @param  array  $cookies
     * @param  array  $files
     * @param  array  $server
     * @param  string $content
     *
     * @return \Illuminate\Http\Response
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->mergeHeaders($server);
        $uri = $this->prepareUri($uri);
        $this->requestCall($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    /**
     * @param $name
     * @param $value
     */
    public function addHeader($name, $value): void
    {
        $this->headers[$name] = $value;
    }

    /**
     * Decode JSON string.
     *
     * @param string $string A JSON string.
     *
     * @return mixed
     * @throws \Exception
     * @see http://www.php.net/json_last_error
     * @deprecated
     */
    protected function decodeJson($string)
    {
        return Helper::decodeJson($string);
    }

    /**
     * @param $fields
     *
     * @return mixed
     * @throws \Exception
     */
    protected function createTestUser($fields)
    {
        if (!isset($fields['username'], $fields['password'])) {
            throw new \Exception('Can not create user. Missing username or password');
        }

        /** @var \App\Models\User $user */
        return factory(User::class)->create([
                                                'username' => $fields['username'],
                                                'password' => Hash::make($fields['password']),
                                            ]);
    }


    /**
     * Load and start lumen application
     */
    protected function startLumen(): void
    {
        if ($this->app && self::$staticApp) {
            return;
        }

        if (self::$staticApp && !$this->app) {
            $this->app = self::$staticApp;

            return;
        }

        if (!$this->basePath) {
            throw new \Exception('Can not start Lumen application without project base path.');
        }

        $lumenBootstrap  = new LumenClient($this->basePath, '.env.testing');
        self::$staticApp = $lumenBootstrap->boot();
        $this->app       = self::$staticApp;
    }

    /**
     * Clear all resolved Lumen instances and unset Lumen application instance
     */
    protected function shutDownLumen(): void
    {
        if (self::$staticApp) {
            Artisan::clearResolvedInstances();
        }

        self::$staticApp = null;
        $this->app       = null;
    }

    /**
     * @param $server
     */
    private function mergeHeaders(&$server): void
    {
        if (!empty($server)) {
            $serverHeaders = $this->transformHeadersToServerVars($this->headers);
            $tmpServer     = array_merge($server, $serverHeaders);
            $server        = $tmpServer;

            return;
        }

        $server = $this->transformHeadersToServerVars($this->headers);
    }

    /**
     * Merge base URL with URL used for request.
     *
     * @param $uri
     *
     * @return string
     */
    private function prepareUri($uri): string
    {
        if (!$this->baseUrl) {
            return '';
        }

        if (strpos($uri, '/') !== 0) {
            $uri = '/' . $uri;
        }

        return $this->baseUrl . $uri;
    }

    public function getToken(): string
    {
        if (!$this->token) {
            $authRequest = new AuthRequestService();
            $this->token = 'Bearer ' . $authRequest->getToken();
        }

        return $this->token;
    }
}