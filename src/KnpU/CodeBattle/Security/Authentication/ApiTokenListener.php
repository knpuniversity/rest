<?php

namespace KnpU\CodeBattle\Security\Authentication;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Responsible for reading the token string off of the Authorization header
 */
class ApiTokenListener implements ListenerInterface
{
    const AUTHORIZATION_HEADER_TOKEN_KEY = 'token';

    private $securityContext;
    private $authenticationManager;

    public function __construct(
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager)
    {
        $this->securityContext       = $securityContext;
        $this->authenticationManager = $authenticationManager;
    }

    public function handle(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();

        // there may not be authentication information on this request
        if (!$request->headers->has('Authorization')) {
            return;
        }

        // format should be "Authorization: token ABCDEFG"
        $authorizationHeader = $request->headers->get('Authorization');
        $tokenString = $this->parseAuthorizationHeader($authorizationHeader);

        // create an object that just exists to hold onto the token string for us
        $token = new ApiAuthToken();
        $token->setAuthToken($tokenString);

        try {
            // this implicitly calls ApiTokenProvider::authenticate($token);
            $returnValue = $this->authenticationManager->authenticate($token);

            if ($returnValue instanceof TokenInterface) {
                return $this->securityContext->setToken($returnValue);
            }
        } catch (AuthenticationException $e) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $event->setResponse($response);
        }
    }

    /**
     * Parses the Authorization header and returns only the token
     *
     * Authorization Header: "token ABCDEFG"
     *
     * will return "ABCDEFG"
     *
     * @param $authorizationHeader
     * @return bool
     */
    private function parseAuthorizationHeader($authorizationHeader)
    {
        $pieces = explode(' ', $authorizationHeader);
        // the format of the authorization header looks wrong
        if (count($pieces) != 2) {
            throw new AuthenticationException('Malformed Authorization header format');
        }

        if ($pieces[0] != self::AUTHORIZATION_HEADER_TOKEN_KEY) {
            // the format is not "token AUTH_TOKEN"
            throw new AuthenticationException(sprintf('Unknown Authorization header type = use "%s"', self::AUTHORIZATION_HEADER_TOKEN_KEY));
        }

        return $pieces[1];
    }
}
