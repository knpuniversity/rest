<?php

namespace KnpU\CodeBattle\Security\Authentication;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Determines the Response that should be back if:
 *
 *  A) There is an authentication error
 *  B) The request requires authentication, but none was provided
 */
class ApiEntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * Starts the authentication scheme.
     *
     * @param Request $request The request that resulted in an AuthenticationException
     * @param AuthenticationException $authException The exception that started the authentication process
     *
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $message = $this->getMessage($authException);

        $response = new JsonResponse(array('details' => $message), 401);

        return $response;
    }

    /**
     * If we have an AuthenticationException, use its getMessageKey().
     *
     * But it should really be run through a translator.
     *
     * Without a translator, InsufficientAuthenticationException is special
     * because it is the exception that occurs when we're denied access,
     * but we're not actually logged in. In this case, we want to simply
     * say "Authentication Required", but the internal message is much
     * uglier than this. So, if we see this exception, we replace the
     * message. If we were using a translator, the ugly message could
     * be replaced with the nice message in the translation dictionary.
     */
    private function getMessage(AuthenticationException $authException = null)
    {
        if ($authException && !$authException instanceof InsufficientAuthenticationException) {
            return $authException->getMessageKey();
        };

        return 'Authentication Required';
    }
}
