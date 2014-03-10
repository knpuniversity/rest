<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Silex\Application;
use Behat\MinkExtension\Context\MinkContext;

use Behat\Behat\Context\Step\Given;
use Behat\Behat\Context\Step\When;
use KnpU\CodeBattle\Model\User;
//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    /**
     * @var Application
     */
    private static $app;

    /**
     * Initializes context.
     * Every scenario gets its own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize your context here
    }

    /**
     * Deletes the database between each scenario, which causes the tables
     * to be re-created and populated with basic fixtures
     *
     * @BeforeScenario
     */
    public function reloadDatabase()
    {
        /** @var \KnpU\CodeBattle\DataFixtures\FixturesManager $fixtures */
        $fixtures = self::$app['fixtures_manager'];

        $fixtures->clearTables();
        $fixtures->populateData();
    }

    /**
     * @BeforeSuite
     */
    public static function bootstrapApp()
    {
        self::$app = require __DIR__.'/../../app/bootstrap.php';
    }

    /**
     * @Given /^I click "([^"]*)"$/
     */
    public function iClick($linkName)
    {
        return new Given(sprintf('I follow "%s"', $linkName));
    }

    /**
     * @Given /^there is a user "([^"]*)" with password "([^"]*)"$/
     */
    public function thereIsAUserWithPassword($email, $plainPassword)
    {
        $this->createUser($email, $plainPassword);
    }

    /**
     * @Given /^I am logged in$/
     */
    public function iAmLoggedIn()
    {
        $this->createUser('ryan@knplabs.com', 'foo');

        return array(
            new Given('I am on "/login"'),
            new Given('I fill in "Email" with "ryan@knplabs.com"'),
            new Given('I fill in "Password" with "foo"'),
            new Given('I press "Login!"'),
        );
    }

    private function createUser($email, $plainPassword)
    {
        $user = new User();
        $user->email = $email;
        $user->username = 'John'.rand(1, 999);
        $user->setPlainPassword($plainPassword);

        self::$app['repository.user']->save($user);
    }
}
