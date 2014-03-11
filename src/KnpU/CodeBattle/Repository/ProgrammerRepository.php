<?php

namespace KnpU\CodeBattle\Repository;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use KnpU\CodeBattle\Model\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class ProgrammerRepository extends BaseRepository
{
    public function findOneByNickname($nickname)
    {
        return $this->findOneBy(array('nickname' => $nickname));
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
