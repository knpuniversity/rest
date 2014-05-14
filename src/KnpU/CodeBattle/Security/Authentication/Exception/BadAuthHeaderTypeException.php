<?php

namespace KnpU\CodeBattle\Security\Authentication\Exception;

use KnpU\CodeBattle\Security\Authentication\ApiTokenListener;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class BadAuthHeaderTypeException extends AuthenticationException
{
    public function getMessageKey()
    {
        return sprintf(
            'Unknown Authorization header type = use "%s"',
            ApiTokenListener::AUTHORIZATION_HEADER_TOKEN_KEY
        );
    }

}
