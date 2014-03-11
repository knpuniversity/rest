<?php

namespace KnpU\CodeBattle\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use KnpU\CodeBattle\Model\Programmer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class BattleController extends BaseController
{
    public function connect(Application $app)
    {
        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->get('/programmer/new', array($this, 'newAction'))->bind('programmer_new');

        return $controllers;
    }

    /**
     * Create a new programmer
     */
    public function newAction()
    {
        $programmer = new Programmer();

        return $this->render('programmer/new.twig', array('programmer' => $programmer));
    }

    /**
     * @return \KnpU\CodeBattle\Repository\BattleRepository
     */
    private function getBattleRepository()
    {
        return $this->container['repository.battle'];
    }
}
