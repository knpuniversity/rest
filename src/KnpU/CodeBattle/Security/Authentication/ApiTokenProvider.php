<?php

namespace KnpU\CodeBattle\Security\Authentication;

use KnpU\CodeBattle\Security\Token\ApiTokenRepository;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use KnpU\CodeBattle\Repository\UserRepository;

/**
 * Responsible for looking up the ApiToken in the database based off of
 * the token string found in ApiTokenListener. If it's found, the related
 * User object is found and authenticated.
 */
class ApiTokenProvider implements AuthenticationProviderInterface
{
    private $userRepository;

    private $apiTokenRepository;

    public function __construct(UserRepository $userRepository, ApiTokenRepository $apiTokenRepository)
    {
        $this->userRepository = $userRepository;
        $this->apiTokenRepository = $apiTokenRepository;
    }

    /**
     * Looks up the token and loads the user based on it
     *
     * @param TokenInterface $token
     * @return ApiAuthToken|TokenInterface
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @throws \Exception
     */
    public function authenticate(TokenInterface $token)
    {
        $apiToken = $this->apiTokenRepository->findOneByToken($token->getCredentials());

        if (!$apiToken) {
            throw new AuthenticationException('Invalid Token');
        }

        $user = $this->userRepository->find($apiToken->userId);
        if (!$user) {
            throw new \Exception('A token without a user? Some crazy things are happening');
        }

        $authenticatedToken = new ApiAuthToken($user->getRoles());
        $authenticatedToken->setUser($user);

        return $authenticatedToken;
    }

    /**
     * Checks whether this provider supports the given token.
     *
     * @param TokenInterface $token A TokenInterface instance
     *
     * @return Boolean true if the implementation supports the Token, false otherwise
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof ApiAuthToken;
    }

} 