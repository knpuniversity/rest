<?php

use Behat\Behat\Context\BehatContext;
use KnpU\CodeBattle\Model\User;
use KnpU\CodeBattle\Model\Programmer;

use KnpU\CodeBattle\Application;

/**
 * Sub-context for interacting with our project
 */
class ProjectContext extends BehatContext
{
    /**
     * @var Application
     */
    private static $app;

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
        $env = 'test';
        self::$app = require __DIR__ . '/../../app/bootstrap.php';
    }

    public function getService($name)
    {
        return self::$app[$name];
    }

    public function createUser($email, $plainPassword)
    {
        $user = new User();
        $user->email = $email;
        $user->username = 'John'.rand(0, 10000);
        $user->setPlainPassword($plainPassword);

        $this->getUserRepository()->save($user);

        return $user;
    }

    public function createProgrammer($nickname, User $owner)
    {
        $programmer = new Programmer();
        $programmer->nickname = $nickname;
        $programmer->userId = $owner->id;
        $programmer->avatar = 'avatar5.jpg';

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
}