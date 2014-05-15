<?php

namespace KnpU\CodeBattle\Twig;

use KnpU\CodeBattle\Model\Battle;
use KnpU\CodeBattle\Model\Programmer;
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
            new \Twig_SimpleFilter('powerLevelClass', array($this, 'getPowerLevelClass')),
            new \Twig_SimpleFilter('avatar_path', array($this, 'getAvatarPath')),
        );
    }

    public function getAssetPath($path)
    {
        return $this->requestStack->getCurrentRequest()->getBasePath().'/'.$path;
    }

    public function getAvatarPath($number)
    {
        return sprintf('img/avatar%s.png', $number);
    }

    public function getPowerLevelClass(Programmer $programmer)
    {
        $powerLevel = $programmer->powerLevel;
        switch (true) {
            case ($powerLevel <= 3):
                return 'danger';
                break;
            case ($powerLevel <= 7):
                return 'warning';
                break;
            default:
                return 'success';
        }
    }

    public function getName()
    {
        return 'code_battle';
    }


}