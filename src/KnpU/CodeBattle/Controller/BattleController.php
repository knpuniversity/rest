<?php

namespace KnpU\CodeBattle\Controller;

use KnpU\CodeBattle\Model\Battle;
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

        $controllers->post('/battle/new', array($this, 'newAction'))->bind('battle_new');
        $controllers->get('/battle/{id}', array($this, 'showAction'))->bind('battle_show');

        return $controllers;
    }

    /**
     * Create a new programmer
     */
    public function newAction(Request $request)
    {
        $programmerId = $request->request->get('programmer_id');
        $projectId = $request->request->get('project_id');
        $programmer = $this->getProgrammerRepository()->find($programmerId);
        $project = $this->getProjectRepository()->find($projectId);

        $battle = $this->getBattleManager()->battle($programmer, $project);

        return $this->redirect($this->generateUrl('battle_show', array('id' => $battle->id)));
    }

    public function showAction($id)
    {
        /** @var Battle $battle */
        $battle = $this->getBattleRepository()->find($id);
        $programmer = $this->getProgrammerRepository()->find($battle->programmerId);
        $project = $this->getProjectRepository()->find($battle->projectId);

        return $this->render('battle/show.twig', array(
            'battle' => $battle,
            'programmer' => $programmer,
            'project' => $project
        ));
    }

    /**
     * @return \KnpU\CodeBattle\Repository\BattleRepository
     */
    private function getBattleRepository()
    {
        return $this->container['repository.battle'];
    }

    /**
     * @return \KnpU\CodeBattle\Repository\ProgrammerRepository
     */
    private function getProgrammerRepository()
    {
        return $this->container['repository.programmer'];
    }

    /**
     * @return \KnpU\CodeBattle\Repository\ProjectRepository
     */
    private function getProjectRepository()
    {
        return $this->container['repository.project'];
    }

    /**
     * @return \KnpU\CodeBattle\Battle\BattleManager
     */
    private function getBattleManager()
    {
        return $this->container['battle.battle_manager'];
    }
}
