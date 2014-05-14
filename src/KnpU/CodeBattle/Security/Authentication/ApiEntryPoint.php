<?php

namespace KnpU\CodeBattle\Security\Authentication;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
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
        if ($authException) {
            $message = $authException->getMessageKey();
        } else {
            $message = 'Authentication Required';
        }

        $response = new JsonResponse(array('details' => $message), 401);

        return $response;
    }

}
