<?php

namespace KnpU\CodeBattle\Controller;

use Silex\Application;
use Silex\ControllerCollection;

class DefaultController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->get('/', array($this, 'homepageAction'))->bind('homepage');
    }

    public function homepageAction()
    {
        return $this->render('homepage.twig');
    }
}
