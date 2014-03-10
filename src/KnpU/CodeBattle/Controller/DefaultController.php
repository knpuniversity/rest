<?php

namespace KnpU\CodeBattle\Controller;

use Silex\Application;
use Silex\ControllerCollection;

class DefaultController extends BaseController
{
    public function connect(Application $app)
    {
        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->get('/', array($this, 'homepageAction'))->bind('homepage');

        return $controllers;
    }

    public function homepageAction()
    {
        return $this->render('homepage.twig');
    }
}
