<?php

namespace KnpU\CodeBattle\Repository;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use KnpU\CodeBattle\Model\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserRepository extends BaseRepository implements UserProviderInterface
{
    /**
     * Injected via setter injection
     *
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    protected function getClassName()
    {
        return 'KnpU\CodeBattle\Model\User';
    }

    protected function getTableName()
    {
        return 'user';
    }

    /**
     * @param $username
     * @return User
     */
    public function findUserByUsername($username)
    {
        return $this->findOneBy(array(
            'username' => $username
        ));
    }

    /**
     * @param $email
     * @return User
     */
    public function findUserByEmail($email)
    {
        return $this->findOneBy(array(
            'email' => $email
        ));
    }

    /**
     * A helper for testing things out - finds any user
     *
     * @return User
     * @throws \Exception
     */
    public function findAny()
    {
        $users = $this->findAllBy(array(), 1);

        if (empty($users)) {
            throw new \Exception('Could not find any users');
        }

        return array_shift($users);
    }

    /**
     * Overridden to encode the password
     *
     * @param $obj
     */
    public function save($obj)
    {
        /** @var User $obj */
        if ($obj->getPlainPassword()) {
            $obj->password = $this->encodePassword($obj, $obj->getPlainPassword());
        }

        parent::save($obj);
    }


    public function loadUserByUsername($username)
    {
        $user = $this->findUserByUsername($username);

        // allow login by email too
        if (!$user) {
            $user = $this->findUserByEmail($username);
        }

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

    /**
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     */
    public function setEncoderFactory($encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    private function encodePassword(User $user, $password)
    {
        $encoder = $this->encoderFactory->getEncoder($user);

        // compute the encoded password for foo
        return $encoder->encodePassword($password, $user->getSalt());
    }
}
