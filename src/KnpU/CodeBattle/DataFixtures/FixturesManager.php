<?php

namespace KnpU\CodeBattle\DataFixtures;

use Silex\Application;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table;
use Symfony\Component\Filesystem\Filesystem;

class FixturesManager
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function resetDatabase()
    {
        // 1) check DB permissions
        $dbPath = $this->app['sqlite_path'];
        $dbDir = dirname($dbPath);

        // make sure the directory is available and writeable
        $filesystem = new Filesystem();
        $filesystem->mkdir($dbDir);
        $filesystem->chmod($dbDir, 0777, 0000, true);

        if (!is_writable($dbDir)) {
            throw new \Exception('Unable to write to '.$dbPath);
        }

        // 2) Add some tables bro!
        $schemaManager = $this->getConnection()->getSchemaManager();

        $userTable = new Table('user');
        $userTable->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
        $userTable->setPrimaryKey(array('id'));
        $userTable->addColumn('email', 'string', array('length' => 255));
        $userTable->addUniqueIndex(array('email'));
        $userTable->addColumn('username', 'string', array('length' => 50));
        $userTable->addUniqueIndex(array('username'));
        $userTable->addColumn('password', 'string', array('length' => 255));
        $schemaManager->dropAndCreateTable($userTable);

        $programmerTable = new Table('programmer');
        $programmerTable->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
        $programmerTable->setPrimaryKey(array('id'));
        $programmerTable->addColumn('nickname', 'string', array('length' => 255));
        $programmerTable->addUniqueIndex(array('nickname'));
        $programmerTable->addColumn('avatar', 'string', array('length' => 255));
        $programmerTable->addColumn('userId', 'integer');
        $programmerTable->addForeignKeyConstraint($userTable, array('userId'), array('id'));
        $schemaManager->dropAndCreateTable($programmerTable);

        $projectTable = new Table('project');
        $projectTable->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
        $projectTable->setPrimaryKey(array('id'));
        $projectTable->addColumn('name', 'string', array('length' => 255));
        $schemaManager->dropAndCreateTable($projectTable);

        $battleTable = new Table('battle');
        $battleTable->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
        $battleTable->setPrimaryKey(array('id'));
        $battleTable->addColumn('userId', 'integer');
        $battleTable->addColumn('projectId', 'integer');
        $battleTable->addColumn('didProgrammerWin', 'integer');
        $battleTable->addColumn('foughtAt', 'datetime');
        $programmerTable->addForeignKeyConstraint($userTable, array('userId'), array('id'));
        $programmerTable->addForeignKeyConstraint($projectTable, array('projectId'), array('id'));
        $schemaManager->dropAndCreateTable($battleTable);
    }

    public function clearTables()
    {
        $schemaManager = $this->getConnection()->getSchemaManager();

        $dbPlatform = $this->getConnection()->getDatabasePlatform();
        $this->getConnection()->beginTransaction();
        foreach ($schemaManager->listTables() as $tbl) {
            $q = $dbPlatform->getTruncateTableSql($tbl->getName());
            $this->getConnection()->executeUpdate($q);
        }
        $this->getConnection()->commit();
    }

    public function populateData()
    {

    }

    /**
     * @return Connection
     */
    private function getConnection()
    {
        return $this->app['db'];
    }
}
