<?php

namespace KnpU\CodeBattle\Security\Token;

use KnpU\CodeBattle\Repository\BaseRepository;
use KnpU\CodeBattle\Model\User;

class ApiTokenRepository extends BaseRepository
{
    const TABLE_NAME = 'api_token';

    protected function getClassName()
    {
        return 'KnpU\CodeBattle\Security\Token\ApiToken';
    }

    protected function getTableName()
    {
        return self::TABLE_NAME;
    }

    /**
     * @param $token
     * @return ApiToken
     */
    public function findOneByToken($token)
    {
        return $this->findOneBy(array('token' => $token));
    }

    public function findAllForUser(User $user)
    {
        return $this->findAllBy(array('userId' => $user->id));
    }

    protected function finishHydrateObject($obj)
    {
        $this->normalizeDateProperty('createdAt', $obj);
    }

    /**
     * Overridden to create our ApiToken even though it has a constructor arg
     *
     * @param string $class
     * @param array $data
     * @return ApiToken
     */
    protected function createObject($class, array $data)
    {
        return new $class($data['userId']);
    }
} 