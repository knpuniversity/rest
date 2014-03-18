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
     * @throws \Exception
     */
    public function save($obj)
    {
        if (!is_object($obj)) {
            throw new \Exception('Expected object, got '.gettype($obj));
        }

        $reflect = new \ReflectionClass($obj);
        $persistedProperties   = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);

        $data = array();
        foreach ($persistedProperties as $prop) {
            $val = $prop->getValue($obj);

            // normalize DateTime objects to string
            if ($val instanceof \DateTime) {
                $val = $val->format('Y-m-d H:i:s');
            }

            $data[$prop->name] = $val;
        }

        if ($obj->id) {

            $this->connection->update(
                $this->getTableName(),
                $data,
                array('id' => $obj->id)
            );
        } else {
            $this->connection->insert(
                $this->getTableName(),
                $data
            );

            $obj->id = $this->connection->lastInsertId();
        }
    }

    public function delete($obj)
    {
        $this->connection->delete($this->getTableName(), array('id' => $obj->id));
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

    public function find($id)
    {
        return $this->findOneBy(array('id' => $id));
    }

    public function findAll()
    {
        return $this->findAllBy(array());
    }

    public function findAllBy(array $criteria, $limit = null)
    {
        $qb = $this->createQueryBuilder('u');
        foreach ($criteria as $key => $val) {
            $qb->andWhere('u.'.$key.' = :'.$key)
                ->setParameter($key, $val)
            ;
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        $stmt = $qb->execute();

        return $this->fetchAllToObject($stmt);
    }

    abstract protected function getClassName();
    abstract protected function getTableName();

    protected function fetchToObject(ResultStatement $stmt)
    {
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // if we don't find any data, just return null
        if (!$data) {
            return null;
        }

        $object = $this->createObjectFromData($data);

        $this->finishHydrateObject($object);

        return $object;
    }

    protected function fetchAllToObject(ResultStatement $stmt)
    {
        $datas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $objects = array();
        foreach ($datas as $data) {
            $object = $this->createObjectFromData($data);
            $this->finishHydrateObject($object);

            $objects[] = $object;
        }

        return $objects;
    }

    /**
     * After we query for the associative array, this creates the right
     * object then populates each public property with that data.
     *
     * This is the heart of our crappy ORM: all properties must be public
     * and they all must have the same name as the column in the database.
     *
     * @param array $data
     * @return object
     */
    private function createObjectFromData(array $data)
    {
        $class = $this->getClassName();
        $object = new $class();
        foreach ($data as $key => $val) {
            $object->$key = $val;
        }

        return $object;
    }

    protected function finishHydrateObject($obj)
    {
        return $obj;
    }

    protected function createQueryBuilder($alias)
    {
        return $this->connection->createQueryBuilder()
            ->select($alias.'.*')
            ->from($this->getTableName(), $alias)
        ;
    }
}
