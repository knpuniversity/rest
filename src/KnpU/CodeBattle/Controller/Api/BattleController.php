<?php

namespace KnpU\CodeBattle\Controller\Api;

use KnpU\CodeBattle\Controller\BaseController;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;

class BattleController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/battles', array($this, 'newAction'));
        $controllers->get('/api/battles/{id}', array($this, 'showAction'))
            ->bind('api_battles_show');
    }

    public function newAction(Request $request)
    {
        $this->enforceUserSecurity();

        $data = $this->decodeRequestBodyIntoParameters($request);

        $programmer = $this->getProgrammerRepository()->find($data->get('programmerId'));
        $project = $this->getProjectRepository()->find($data->get('projectId'));

        if (!$programmer || !$project) {
            $this->throwApiProblemValidationException(array(
                'Invalid programmerId or projectId',
            ));
        }

        // make sure I own this programmer
        $this->enforceProgrammerOwnershipSecurity($programmer);

        $battle = $this->getBattleManager()->battle($programmer, $project);

        $response = $this->createApiResponse($battle, 201);
        $response->headers->set('Location', 'TODO');

        return $response;
    }

    public function showAction($id)
    {
        $battle = $this->getBattleRepository()->find($id);
        if (!$battle) {
            $this->throw404('No battle with id '.$id);
        }

        $response = $this->createApiResponse($battle, 200);

        return $response;
    }
}
