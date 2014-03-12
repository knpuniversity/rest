<?php

namespace KnpU\CodeBattle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class Programmer
{

    /* All public properties are persisted */
    public $id;

    public $nickname;

    public $avatar;

    public $userId;

    public $powerLevel = 0;
}
