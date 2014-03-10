<?php

namespace KnpU\CodeBattle\Repository;

use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use KnpU\CodeBattle\Model\User;

class UserRepository implements UserProviderInterface
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param $username
     * @return User
     */
    public function findUserByUsername($username)
    {
        throw new \Exception('todo');
    }

    public function loadUserByUsername($username)
    {
        $user = $this->findUserByUsername($username);

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('Email "%s" does not exist.', $username));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'KnpU\CodeBattle\Model\User';
    }
}
