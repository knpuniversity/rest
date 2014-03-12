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
use Behat\Behat\Context\Step\Then;
use KnpU\CodeBattle\Model\User;
use KnpU\CodeBattle\Model\Programmer;
use KnpU\CodeBattle\Model\Project;
//
// Require 3rd-party libraries here:
//
require_once __DIR__.'/../../vendor/phpunit/phpunit/PHPUnit/Autoload.php';
require_once __DIR__.'/../../vendor/phpunit/phpunit/PHPUnit/Framework/Assert/Functions.php';

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
     * @var User
     */
    private $currentUser;

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
    }

    /**
     * @BeforeSuite
     */
    public static function bootstrapApp()
    {
        $env = 'test';
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
     * @Given /^I select an avatar$/
     *
     * Used on the create programmer page
     *
     */
    public function iSelectAnAvatar()
    {
        $this->getSession()->getPage()->find('css', '.js-selectable-tile li')->click();
    }

    /**
     * @Given /^I click on a project$/
     */
    public function iClickOnAProject()
    {
        $this->getSession()->getPage()->find('css', '.projects-list .js-select-battle-project')->press();
    }


    /**
     * @Given /^I am logged in$/
     */
    public function iAmLoggedIn()
    {
        $this->currentUser = $this->createUser('ryan@knplabs.com', 'foo');

        return array(
            new Given('I am on "/login"'),
            new Given('I fill in "Email" with "ryan@knplabs.com"'),
            new Given('I fill in "Password" with "foo"'),
            new Given('I press "Login!"'),
        );
    }

    /**
     * @Given /^I created the following programmers$/
     */
    public function iCreatedTheFollowingProgrammers(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $this->createProgrammer($row['nickname'], $this->currentUser);
        }
    }

    /**
     * @Given /^the following projects exist$/
     */
    public function theFollowingProjectsExist(TableNode $table)
    {
        $projectRepo = $this->getProjectRepository();
        foreach ($table->getHash() as $row) {
            $project = new Project();
            $project->name = $row['name'];
            $project->difficultyLevel = rand(1, 10);
            $projectRepo->save($project);
        }
    }

    /**
     * @Given /^someone else created a programmer named "([^"]*)"$/
     */
    public function someoneElseCreatedAProgrammerNamed($nickname)
    {
        $user = $this->createUser('foo'.rand(0, 999).'@bar.com', 'foobar');

        $this->createProgrammer($nickname, $user);
    }

    /**
     * @Then /^I should see (\d+) programmers in the list$/
     */
    public function iShouldSeeProgrammersInTheList($count)
    {
        $programmerList = $this->getSession()
            ->getPage()
            ->findAll('css', '.programmers-list li')
        ;

        assertNotNull($programmerList, 'Cannot see the programmer list');

        assertCount(intval($count), $programmerList);
    }

    /**
     * @Then /^I should see (\d+) projects in the list$/
     */
    public function iShouldSeeProjectsInTheList($count)
    {
        $projectList = $this->getSession()
            ->getPage()
            ->findAll('css', '.projects-list li')
        ;

        assertNotNull($projectList, 'Cannot see the project list');

        assertCount(intval($count), $projectList);
    }

    /**
     * @Given /^the following battles have been valiantly fought:$/
     */
    public function theFollowingBattlesHaveBeenValiantlyFought(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $programmer = $this->getProgrammerRepository()->findOneByNickname($row['programmer']);
            $project = $this->getProjectRepository()->findOneByName($row['project']);

            $battle = self::$app['battle.battle_manager']->battle($programmer, $project);
        }
    }

    /**
     * @Then /^I should see a table with (\d+) rows$/
     */
    public function iShouldSeeATableWithRows($rowCount)
    {
        $tbl = $this->getSession()->getPage()->find('css', '.main-console-screen table.table');
        assertNotNull($tbl, 'Cannot find a table!');

        assertCount(intval($rowCount), $tbl->findAll('css', 'tbody tr'));
    }

    /**
     * @Then /^I should see a flash message containing "([^"]*)"$/
     */
    public function iShouldSeeAFlashMessageContaining($text)
    {
        return new Then(sprintf('the ".flash-message" element should contain "%s"', $text));
    }

    /**
     * @Given /^I wait for the dialog to appear$/
     */
    public function iWaitForTheDialogToAppear()
    {
        $this->getSession()->wait(
            5000,
            "jQuery('.modal').is(':visible');"
        );
    }

    /**
     * @Then /^(?:|I )break$/
     */
    public function addABreakpoint()
    {
        fwrite(STDOUT, "\033[s    \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue...\033[0m");
        while (fgets(STDIN, 1024) == '') {}
        fwrite(STDOUT, "\033[u");

        return;
    }

    private function createUser($email, $plainPassword)
    {
        $user = new User();
        $user->email = $email;
        $user->username = 'John'.rand(0, 10000);
        $user->setPlainPassword($plainPassword);

        self::$app['repository.user']->save($user);

        return $user;
    }

    private function createProgrammer($nickname, User $owner)
    {
        $programmer = new Programmer();
        $programmer->nickname = $nickname;
        $programmer->userId = $owner->id;
        $programmer->avatar = 'avatar5.jpg';

        $this->getProgrammerRepository()->save($programmer);

        return $programmer;
    }

    /**
     * @return \KnpU\CodeBattle\Repository\ProgrammerRepository
     */
    private function getProgrammerRepository()
    {
        return self::$app['repository.programmer'];
    }

    /**
     * @return \KnpU\CodeBattle\Repository\ProjectRepository
     */
    private function getProjectRepository()
    {
        return self::$app['repository.project'];
    }
}
