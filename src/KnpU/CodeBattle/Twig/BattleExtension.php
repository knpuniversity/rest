<?php

namespace KnpU\CodeBattle\Twig;

use KnpU\CodeBattle\Model\Battle;
use KnpU\CodeBattle\Repository\ProgrammerRepository;
use KnpU\CodeBattle\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig_SimpleFunction;

class BattleExtension extends \Twig_Extension
{
    private $requestStack;

    private $programmerRepository;

    private $projectRepository;

    public function __construct(RequestStack $requestStack, ProgrammerRepository $programmerRepository, ProjectRepository $projectRepository)
    {
        $this->requestStack = $requestStack;
        $this->programmerRepository = $programmerRepository;
        $this->projectRepository = $projectRepository;

    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('asset', array($this, 'getAssetPath')),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('programmer', array($this, 'getProgrammer')),
            new \Twig_SimpleFilter('project', array($this, 'getProject')),
        );
    }

    public function getProject(Battle $battle, $property = null)
    {
        $project = $this->projectRepository->find($battle->projectId);

        return $property ? $project->$property : $project;
    }

    public function getProgrammer(Battle $battle, $property = null)
    {
        $programmer = $this->programmerRepository->find($battle->programmerId);

        return $property ? $programmer->$property : $programmer;
    }

    public function getAssetPath($path)
    {
        return $this->requestStack->getCurrentRequest()->getBasePath().'/'.$path;
    }

    public function getName()
    {
        return 'code_battle';
    }


}