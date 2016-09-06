<?php

namespace KnpU\CodeBattle\Controller;

use KnpU\CodeBattle\Model\User;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;

class UserController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->get('/register', array($this, 'registerAction'))->bind('user_register');
        $controllers->post('/register', array($this, 'registerHandleAction'))->bind('user_register_handle');
        $controllers->get('/login', array($this, 'loginAction'))->bind('user_login');
        $controllers->post('/login_check', array($this, 'loginCheckAction'))->bind('user_login_check');
        $controllers->get('/logout', array($this, 'logoutAction'))->bind('user_logout');
    }

    /**
     * Registration page
     */
    public function registerAction()
    {
        if ($this->isUserLoggedIn()) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('user/register.twig', array('user' => new User()));
    }

    /**
     * Processes the registration
     */
    public function registerHandleAction(Application $app, Request $request)
    {
        $errors = array();

        if (!$email = $request->request->get('email')) {
            $errors[] = '"email" is required';
        }
        if (!$plainPassword = $request->request->get('plainPassword')) {
            $errors[] = '"password" is required';
        }
        if (!$username = $request->request->get('username')) {
            $errors[] = '"username" is required';
        }

        /** @var \KnpU\CodeBattle\Repository\UserRepository $userRepository */
        $userRepository = $app['repository.user'];

        // make sure we don't already have this user!
        if ($existingUser = $userRepository->findUserByEmail($email)) {
            $errors[] = 'A user with this email is already registered!';
        }

        // make sure we don't already have this user!
        if ($existingUser = $userRepository->findUserByUsername($username)) {
            $errors[] = 'A user with this username is already registered!';
        }

        $user = new User();
        $user->email = $email;
        $user->username = $username;
        $user->setPlainPassword($plainPassword);

        // errors? Show them!
        if (count($errors) > 0) {
            return $this->render('user\register.twig', array('errors' => $errors, 'user' => $user));
        }

        $userRepository->save($user);
        $this->loginUser($user);

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * Displays the login form
     *
     * @param Application $app
     */
    public function loginAction(Application $app, Request $request)
    {
        if ($this->isUserLoggedIn()) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('user/login.twig', array(
            'error'         => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
        ));
    }

    public function loginCheckAction(Application $app)
    {
        throw new \Exception('Should not get here - this should be handled magically by the security system!');
    }

    public function logoutAction(Application $app)
    {
        throw new \Exception('Should not get here - this should be handled magically by the security system!');
    }
}
