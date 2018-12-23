<?php

use Behat\Behat\Context\Context;
use App\Models\BehatIntegration\LumenMicroService;
use App\Models\BehatIntegration\DatabaseMigrations;
use Nmi\Authjwt\AuthCheckerService;

/**
 * MicroServiceContext class with additional steps used in MicroServices.
 */
class MicroServiceContext extends LumenMicroService implements Context
{
    use DatabaseMigrations;

    /**
     * @Then /^response status is (\d+)$/
     * @param $status
     */
    public function responseStatusIs($status) {
        $this->assertResponseStatus($status);
    }

    /**
     * @Given /^I am authenticated as "([^"]*)"$/
     *
     * Creates test user and logs with him, generating a token that is added to HTTP headers and used later for authorization.
     * @param string $username
     * @throws Exception
     */
    public function iAmAuthenticatedAs($username)
    {
        $authentication = new AuthCheckerService();
        $token = config("behat.$username.token");
        $authentication->validateToken($token);

        $this->addHeader('Authorization','Bearer '.$token);
    }
}