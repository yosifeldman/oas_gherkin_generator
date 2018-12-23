<?php

namespace App\Models\BehatIntegration;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;

/**
 * Class DatabaseTransactions
 * Set up database connection within transaction before scenario starts and rollback all changes after scenario ends.
 * @package App\Models\BehatIntegration
 */
trait DatabaseTransactions
{

    /**
     * Database connection
     * @var mixed
     */
    protected $database;

    /** @BeforeScenario */
    public function before(BeforeScenarioScope $scope)
    {
        $this->database = $this->app->make('db');

        foreach ($this->connectionsToTransact() as $name) {
            $this->database->connection($name)->beginTransaction();
        }
    }

    /** @AfterScenario */
    public function after(AfterScenarioScope $scope)
    {
        foreach ($this->connectionsToTransact() as $name) {
            $this->database->connection($name)->rollBack();
        }
    }

    /**
     * The database connections that should have transactions.
     * @return array
     */
    protected function connectionsToTransact()
    {
        return property_exists($this, 'connectionsToTransact')
            ? $this->connectionsToTransact : [null];
    }
}