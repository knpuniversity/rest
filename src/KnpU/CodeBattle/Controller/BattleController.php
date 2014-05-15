<?php

namespace KnpU\CodeBattle\Controller;

use KnpU\CodeBattle\Model\Battle;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;


class BattleController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/battles/new', array($this, 'newAction'))->bind('battle_new');
        $controllers->get('/battles/{id}', array($this, 'showAction'))->bind('battle_show');
        $controllers->get('/battles', array($this, 'listAction'))->bind('battle_list');
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

        if ($programmer->userId != $this->getLoggedInUser()->id) {
            throw new AccessDeniedException();
        }

        $battle = $this->getBattleManager()->battle($programmer, $project);

        return $this->redirect($this->generateUrl('battle_show', array('id' => $battle->id)));
    }

    public function showAction($id)
    {
        /** @var Battle $battle */
        $battle = $this->getBattleRepository()->find($id);

        return $this->render('battle/show.twig', array(
            'battle' => $battle,
            'programmer' => $battle->programmer,
            'project' => $battle->project,
        ));
    }

    public function listAction()
    {
        $battles = $this->getBattleRepository()->findAll();

        return $this->render('battle/list.twig', array(
            'battles' => $battles,
        ));
    }
}
