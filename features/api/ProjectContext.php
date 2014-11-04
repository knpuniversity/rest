<?php

use Behat\Behat\Context\BehatContext;
use KnpU\CodeBattle\Model\User;
use KnpU\CodeBattle\Model\Programmer;
use Behat\Gherkin\Node\TableNode;
use KnpU\CodeBattle\Security\Token\ApiToken;
use KnpU\CodeBattle\Application;
use KnpU\CodeBattle\Model\Project;

/**
 * Sub-context for interacting with our project
 */
class ProjectContext extends BehatContext
{
    /**
     * @var Application
     */
    private static $app;

    private $lastBattle;

    /**
     * @Given /^the user "([^"]*)" exists$/
     */
    public function theUserExists($username)
    {
        $this->thereIsAUserWithPassword($username, 'foo');
    }

    /**
     * @Given /^there is a user "([^"]*)" with password "([^"]*)"$/
     */
    public function thereIsAUserWithPassword($username, $password)
    {
        $this->createUser($username.'@foo.com', $password, $username);
    }

    /**
     * @Given /^the following programmers exist:$/
     */
    public function theFollowingProgrammersExist(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $nickname = $row['nickname'];
            unset($row['nickname']);

            $this->createProgrammer($nickname, null, $row);
        }
    }

    /**
     * @Given /^there is a programmer called "([^"]*)"$/
     */
    public function thereIsAProgrammerCalled($name)
    {
        $this->createProgrammer($name);
    }

    /**
     * @Given /^"([^"]*)" has an authentication token "([^"]*)"$/
     */
    public function hasAnAuthenticationToken($username, $tokenString)
    {
        $user = $this->getUserRepository()->findUserByUsername($username);
        if (!$user) {
            throw new \Exception(sprintf('Cannot find user '.$username));
        }

        $token = new ApiToken($user->id);
        $token->notes = 'Behat testing!';
        $token->token = $tokenString;

        $this->getApiTokenRepository()->save($token);
    }

    /**
     * @Given /^there is a project called "([^"]*)"$/
     */
    public function thereIsAProjectCalled($name)
    {
        $project = new Project();
        $project->name = $name;
        $project->difficultyLevel = rand(1, 10);

        $this->getProjectRepository()->save($project);
    }

    /**
     * @Given /^there has been a battle between "([^"]*)" and "([^"]*)"$/
     */
    public function thereHasBeenABattleBetweenAnd($programmerName, $projectName)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($programmerName);
        $project = $this->getProjectRepository()->findOneByName($projectName);

        $this->lastBattle = $this->getBattleManager()->battle($programmer, $project);
    }

    /**
     * Can be used with a BeforeScenario hook to clear the data between scenarios
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
        self::$app = require __DIR__ . '/../../app/bootstrap.php';
    }

    public function getService($name)
    {
        return self::$app[$name];
    }

    public function createUser($email, $plainPassword, $username = null)
    {
        $user = new User();
        $user->email = $email;
        $user->username = $username ? $username : 'John'.rand(0, 10000);
        $user->setPlainPassword($plainPassword);

        $this->getUserRepository()->save($user);

        return $user;
    }

    public function createProgrammer($nickname, User $owner = null, array $data = array())
    {
        $avatarNumber = isset($data['avatarNumber']) ? $data['avatarNumber'] : rand(1, 6);
        $programmer = new Programmer($nickname, $avatarNumber);

        $data = array_merge(array(
            'powerLevel' => rand(0, 10),
        ), $data);

        foreach ($data as $prop => $val) {
            $programmer->$prop = $val;
        }

        if (!$owner) {
            $owner = $this->getUserRepository()->findAny();
        }
        $programmer->userId = $owner->id;

        $this->getProgrammerRepository()->save($programmer);

        return $programmer;
    }

    /**
     * @return \KnpU\CodeBattle\Battle\BattleManager
     */
    public function getBattleManager()
    {
        return $this->getService('battle.battle_manager');
    }

    /**
     * @return \KnpU\CodeBattle\Repository\ProgrammerRepository
     */
    public function getProgrammerRepository()
    {
        return self::$app['repository.programmer'];
    }

    /**
     * @return \KnpU\CodeBattle\Repository\ProjectRepository
     */
    public function getProjectRepository()
    {
        return self::$app['repository.project'];
    }

    /**
     * @return \KnpU\CodeBattle\Repository\UserRepository
     */
    public function getUserRepository()
    {
        return self::$app['repository.user'];
    }

    /**
     * @return \KnpU\CodeBattle\Security\Token\ApiTokenRepository
     */
    public function getApiTokenRepository()
    {
        return self::$app['repository.api_token'];
    }
}