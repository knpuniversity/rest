<?php

namespace KnpU\CodeBattle\Controller\Api;

use KnpU\CodeBattle\Controller\BaseController;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use KnpU\CodeBattle\Security\Token\ApiToken;

class TokenController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/tokens', array($this, 'newAction'));
    }

    public function newAction(Request $request)
    {
        $this->enforceUserSecurity();

        $data = json_decode($request->getContent(), true);

        $token = new ApiToken($this->getLoggedInUser()->id);
        $token->notes = $data['notes'];

        $this->getApiTokenRepository()->save($token);

        return $this->createApiResponse($token, 201);
    }
}
