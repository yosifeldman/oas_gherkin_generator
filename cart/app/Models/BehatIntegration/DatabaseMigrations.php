<?php

namespace App\Models\BehatIntegration;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Illuminate\Support\Facades\Artisan;

trait DatabaseMigrations
{

    /**
     * Database used in migrations
     * @var string
     */
    protected $migrationDatabaseName = 'testing';

    /** @BeforeScenario */
    public function before(BeforeScenarioScope $scope)
    {
        $this->migrate();
    }

    /**
     * Migrate testing database and seed with data
     */
    public function migrate()
    {
        Artisan::call('migrate', ['--database' => $this->migrationDatabaseName]);
        Artisan::call('db:seed');
    }

    /**
     * Rollback testing database migrations
     */
    public function rollback()
    {
        Artisan::call('migrate:rollback', ['--database' => $this->migrationDatabaseName]);
    }
}
