<?php

namespace KnpU\CodeBattle\Controller\Api;

use KnpU\CodeBattle\Controller\BaseController;
use KnpU\CodeBattle\Model\Programmer;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProgrammerController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/programmers', array($this, 'newAction'));

        $controllers->get('/api/programmers/{nickname}', array($this, 'showAction'))
            ->bind('api_programmers_show');

        $controllers->get('/api/programmers', array($this, 'listAction'));
        $controllers->put('/api/programmers/{nickname}', array($this, 'updateAction'));
        $controllers->delete('/api/programmers/{nickname}', array($this, 'deleteAction'));
    }

    public function newAction(Request $request)
    {
        $programmer = new Programmer();
        $this->handleRequest($request, $programmer);
        $this->save($programmer);

        $data = $this->serializeProgrammer($programmer);
        $response = new JsonResponse($data, 201);
        $programmerUrl = $this->generateUrl(
            'api_programmers_show',
            ['nickname' => $programmer->nickname]
        );
        $response->headers->set('Location', $programmerUrl);

        return $response;
    }

    public function showAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);

        if (!$programmer) {
            $this->throw404();
        }

        $data = $this->serializeProgrammer($programmer);

        $response = new JsonResponse($data, 200);

        return $response;
    }

    public function listAction()
    {
        $programmers = $this->getProgrammerRepository()->findAll();
        $data = array('programmers' => array());
        foreach ($programmers as $programmer) {
            $data['programmers'][] = $this->serializeProgrammer($programmer);
        }

        $response = new JsonResponse($data, 200);

        return $response;
    }

    public function updateAction($nickname, Request $request)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);

        if (!$programmer) {
            $this->throw404();
        }

        $this->handleRequest($request, $programmer);
        $this->save($programmer);

        $data = $this->serializeProgrammer($programmer);

        $response = new JsonResponse($data, 200);

        return $response;
    }

    public function deleteAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);

        if ($programmer) {
            $this->delete($programmer);
        }

        return new Response(null, 204);
    }

    /**
     * Reads data from the Request, updates the Programmer and saves it.
     *
     * @param Request $request
     * @param Programmer $programmer
     */
    private function handleRequest(Request $request, Programmer $programmer)
    {
        $data = json_decode($request->getContent(), true);
        $isNew = !$programmer->id;

        if ($data === null) {
            throw new \Exception(sprintf('Invalid JSON: '.$request->getContent()));
        }

        // determine which properties should be changeable on this request
        $apiProperties = array('avatarNumber', 'tagLine');
        if ($isNew) {
            $apiProperties[] = 'nickname';
        }

        // update the properties
        foreach ($apiProperties as $property) {
            $val = isset($data[$property]) ? $data[$property] : null;
            $programmer->$property = $val;
        }

        $programmer->userId = $this->findUserByUsername('weaverryan')->id;
    }

    private function serializeProgrammer(Programmer $programmer)
    {
        return array(
            'nickname' => $programmer->nickname,
            'avatarNumber' => $programmer->avatarNumber,
            'powerLevel' => $programmer->powerLevel,
            'tagLine' => $programmer->tagLine,
        );
    }
}
