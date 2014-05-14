<?php

namespace KnpU\CodeBattle\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Token used internally by the security system - it just stores the token
 * string temporarily.
 */
class ApiAuthToken extends AbstractToken
{
    private $token;

    public function setAuthToken($token)
    {
        $this->token = $token;
    }

    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        return $this->token;
    }

}
