<?php

namespace KnpU\CodeBattle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class Programmer
{

    /* All public properties are persisted */
    public $id;

    public $nickname;

    /**
     * Number of an avatar, from 1-9
     *
     * @var integer
     */
    public $avatarNumber;

    public $userId;

    public $powerLevel = 0;
}
