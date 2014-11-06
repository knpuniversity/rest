<?php

namespace KnpU\CodeBattle\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use PDO;

abstract class BaseRepository
{
    protected $connection;

    private $repoContainer;

    public function __construct(Connection $connection, RepositoryContainer $repoContainer)
    {
        $this->connection = $connection;
        $this->repoContainer = $repoContainer;
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
            $columnName = $prop->name;

            // normalize DateTime objects to string
            if ($val instanceof \DateTime) {
                $val = $val->format('Y-m-d H:i:s');
            } elseif (is_object($val)) {
                // process a relationship
                if (!property_exists(get_class($val), 'id')) {
                    throw new \Exception(sprintf(
                        'Property "%s" is an object, but it doesn\'t look like a relationship',
                        $prop->name
                    ));
                }

                // programmer becomes programmerId in the database
                $columnName = $columnName.'Id';
                $val = $val->id;
            }

            $data[$columnName] = $val;
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

    /**
     * @return object
     */
    public function findLast()
    {
        $stmt = $this->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC')
            ->setMaxResults(1)
            ->execute();

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

    public function findAllLike(array $criteria, $limit = null)
    {
        return $this->findAllBy($criteria, $limit, 'LIKE');
    }

    public function findAllBy(array $criteria, $limit = null, $operator = '=')
    {
        $qb = $this->createQueryBuilder('u');
        foreach ($criteria as $key => $val) {
            $qb->andWhere('u.'.$key.' '.$operator.' :'.$key)
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
        if (!class_exists($class)) {
            throw new \Exception(sprintf('Repository class %s is returning a bad getClassName: "%s"', get_class($this), $this->getClassName()));
        }

        $object = $this->createObject($class, $data);
        foreach ($data as $key => $val) {
            $columnName = $key;

            if (substr($columnName, -2) == 'Id' && !property_exists($class, $columnName)) {
                // does it end in Id, like programmerId? (and that property doesn't exist)

                // make programmerId -> programmer
                $columnName = substr($columnName, 0, -2);
                $obj = $this->repoContainer->get($columnName)->find($val);
                if (!$obj) {
                    throw new \Exception(sprintf(
                        'Could not query for foreign key object %s with id %s',
                        $key,
                        $val
                    ));
                }

                $val = $obj;
            }

            $object->$columnName = $val;
        }

        return $object;
    }

    protected function createObject($class, array $data)
    {
        return new $class();
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

    /**
     * Can be called in finishHydrateObject to normalize a date to a DateTime object
     *
     * @param $propertyName
     * @param $obj
     */
    protected function normalizeDateProperty($propertyName, $obj)
    {
        $obj->$propertyName = \DateTime::createFromFormat('Y-m-d H:i:s', $obj->$propertyName);
    }
}
