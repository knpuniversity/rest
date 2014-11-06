<?php

namespace KnpU\CodeBattle\Controller\Api;

use KnpU\CodeBattle\Controller\BaseController;
use Silex\ControllerCollection;

class BattleController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/battles', array($this, 'newAction'));
    }

    public function newAction()
    {
        return 'foo';
    }
}
