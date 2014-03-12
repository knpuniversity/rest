<?php

namespace KnpU\CodeBattle\Repository;

use KnpU\CodeBattle\Model\Programmer;
use KnpU\CodeBattle\Model\User;

class ProgrammerRepository extends BaseRepository
{
    /**
     * @param $nickname
     * @return Programmer
     */
    public function findOneByNickname($nickname)
    {
        return $this->findOneBy(array('nickname' => $nickname));
    }

    public function findAllForUser(User $user)
    {
        return $this->findAllBy(array('userId' => $user->id));
    }

    protected function getClassName()
    {
        return 'KnpU\CodeBattle\Model\Programmer';
    }

    protected function getTableName()
    {
        return 'programmer';
    }
}
