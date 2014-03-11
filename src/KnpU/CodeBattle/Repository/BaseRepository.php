<?php

namespace KnpU\CodeBattle\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use PDO;

abstract class BaseRepository
{
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Saves the object (the public properties are persisted)
     *
     * @param $obj
     */
    public function save($obj)
    {
        $reflect = new \ReflectionClass($obj);
        $persistedProperties   = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);

        $data = array();
        foreach ($persistedProperties as $prop) {
            $data[$prop->name] = $prop->getValue($obj);
        }

        if ($obj->id) {
            $this->connection->update(
                $this->getTableName(),
                $data,
                $obj->id
            );
        } else {
            $this->connection->insert(
                $this->getTableName(),
                $data
            );
        }
    }

    /**
     * @param array $criteria
     * @return object
     */
    public function findOneBy(array $criteria)
    {
        $qb = $this->createQueryBuilder('u');
        foreach ($criteria as $key => $val) {
            $qb->andWhere('u.'.$key.' = :'.$key)
                ->setParameter($key, $val)
            ;
        }

        $stmt = $qb->execute();

        return $this->fetchToObject($stmt);
    }

    abstract protected function getClassName();
    abstract protected function getTableName();

    protected function fetchToObject(ResultStatement $stmt)
    {
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->getClassName());

        return $stmt->fetch(PDO::FETCH_CLASS);
    }

    protected function createQueryBuilder($alias)
    {
        return $this->connection->createQueryBuilder()
            ->select($alias.'.*')
            ->from($this->getTableName(), $alias)
        ;
    }
}
