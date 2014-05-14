<?php

namespace KnpU\CodeBattle\Security\Authentication;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Translation\Translator;

/**
 * Determines the Response that should be back if:
 *
 *  A) There is an authentication error
 *  B) The request requires authentication, but none was provided
 */
class ApiEntryPoint implements AuthenticationEntryPointInterface
{
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

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

        $response = new JsonResponse(array('detail' => $message), 401);

        return $response;
    }

    /**
     * Gets the message from the specific AuthenticationException and then
     * translates it. The translation process allows us to customize the
     * messages we want - see the translations/en.yml file.
     */
    private function getMessage(AuthenticationException $authException = null)
    {
        $key = $authException ? $authException->getMessageKey() : 'authentication_required';
        $parameters = $authException ? $authException->getMessageData() : array();

        return $this->translator->trans($key, $parameters);
    }
}
