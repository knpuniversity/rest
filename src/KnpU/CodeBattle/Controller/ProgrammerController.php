<?php

namespace KnpU\CodeBattle\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use KnpU\CodeBattle\Model\Programmer;
use Symfony\Component\HttpFoundation\Request;


class ProgrammerController extends BaseController
{
    public function connect(Application $app)
    {
        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->get('/programmer/new', array($this, 'programmerNewAction'))->bind('programmer_new');
        $controllers->post('/programmer/new', array($this, 'programmerNewHandleAction'))->bind('programmer_new_handle');
        $controllers->get('/programmer/show', array($this, 'programmerShowAction'))->bind('programmer_show');

        return $controllers;
    }

    /**
     * Create a new programmer
     */
    public function programmerNewAction()
    {
        $programmer = new Programmer();

        return $this->render('programmer/new.twig', array('programmer' => $programmer));
    }

    /**
     * Create a new programmer
     */
    public function programmerNewHandleAction(Request $request)
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

        return $this->redirect($this->generateUrl('programmer_show'));
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
}
