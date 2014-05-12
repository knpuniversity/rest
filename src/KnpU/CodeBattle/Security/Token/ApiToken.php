<?php

namespace KnpU\CodeBattle\Model;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ApiToken
{

    /* All public properties are persisted */
    public $id;

    public $token;

    public $userId;

    public $enabled = true;
}
