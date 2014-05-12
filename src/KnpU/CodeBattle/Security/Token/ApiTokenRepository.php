<?php

namespace KnpU\CodeBattle\Security\Token;

use KnpU\CodeBattle\Model\ApiToken;
use KnpU\CodeBattle\Model\User;
use KnpU\CodeBattle\Repository\BaseRepository;

class ApiTokenRepository extends BaseRepository
{
    const TABLE_NAME = 'api_token';

    protected function getClassName()
    {
        // TODO: Implement getClassName() method.
    }

    protected function getTableName()
    {
        return self::TABLE_NAME;
    }

    /**
     * @param User $user
     * @return ApiToken
     */
    public function createKey(User $user)
    {
        $token = new ApiToken();
        $token->enabled = true;
        $token->token = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $token->userId = $user->id;

        $this->save($token);

        return $token;
    }

    /**
     * @param $token
     * @return ApiToken
     */
    public function findOneByToken($token)
    {
        return $this->findOneBy(array('token' => $token));
    }
} 