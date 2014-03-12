<?php

namespace KnpU\CodeBattle\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use KnpU\CodeBattle\Model\Programmer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class ProgrammerController extends BaseController
{
    public function connect(Application $app)
    {
        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->get('/programmer/new', array($this, 'newAction'))->bind('programmer_new');
        $controllers->post('/programmer/new', array($this, 'handleNewAction'))->bind('programmer_new_handle');
        $controllers->get('/programmer/choose', array($this, 'chooseAction'))->bind('programmer_choose');
        $controllers->get('/programmer/{nickname}', array($this, 'showAction'))->bind('programmer_show');

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
     * Create a new programmer
     */
    public function handleNewAction(Request $request)
    {
        $programmer = new Programmer();

        $errors = array();
        $data = $this->getAndValidateData($request, $errors);
        $programmer->nickname = $data['nickname'];
        $programmer->avatar = $data['avatar'];
        $programmer->userId = $this->getLoggedInUser()->id;

        if ($errors) {
            return $this->render('programmer/new.twig', array('programmer' => $programmer, 'errors' => $errors));
        }

        $this->getProgrammerRepository()->save($programmer);

        $this->setFlash(sprintf('%s has been compiled and is ready for battle!', $programmer->nickname));
        return $this->redirect($this->generateUrl('programmer_show', array('nickname' => $programmer->nickname)));
    }

    public function showAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);
        if (!$programmer) {
            throw new NotFoundHttpException();
        }

        $projects = $this->getProjectRepository()->findRandom(3);

        return $this->render('programmer/show.twig', array(
            'programmer' => $programmer,
            'projects' => $projects,
        ));
    }

    public function chooseAction()
    {
        $programmers = $this->getProgrammerRepository()->findAllForUser($this->getLoggedInUser());

        return $this->render('programmer/choose.twig', array('programmers' => $programmers));
    }

    /**
     * @param Request $request
     * @param array $errors Array that will be filled with errors (I hate
     *                      passing things by reference, but it makes this simple)
     * @return array
     */
    private function getAndValidateData(Request $request, &$errors)
    {
        $nickname = $request->request->get('nickname');
        $avatar = $request->request->get('avatar');

        $errors = array();
        if (!$nickname) {
            $errors[] = 'Give your programmer a nickname!';
        }
        if (!$avatar) {
            $errors[] = 'Choose an awesome avatar bro!';
        }

        $existingProgrammer = $this->getProgrammerRepository()->findOneByNickname($nickname);
        if ($existingProgrammer) {
            $errors[] = 'Looks like that programmer already exists - try a different nickname';
        }

        return array('nickname' => $nickname, 'avatar' => $avatar);
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
}
