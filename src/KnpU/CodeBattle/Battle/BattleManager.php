<?php

namespace KnpU\CodeBattle\Battle;

use KnpU\CodeBattle\Model\Battle;
use KnpU\CodeBattle\Model\Programmer;
use KnpU\CodeBattle\Model\Project;
use KnpU\CodeBattle\Repository\BattleRepository;

class BattleManager
{
    private $battleRepository;

    public function __construct(BattleRepository $battleRepository)
    {
        $this->battleRepository = $battleRepository;
    }

    /**
     * Creates and wages an epic battle
     *
     * @param Programmer $programmer
     * @param Project $project
     * @return Battle
     */
    public function battle(Programmer $programmer, Project $project)
    {
        $battle = new Battle();
        $battle->programmerId = $programmer->id;
        $battle->projectId = $project->id;
        $battle->foughtAt = new \DateTime();
        $battle->didProgrammerWin = rand(0, 2) != 2;

        $this->battleRepository->save($battle);

        return $battle;
    }
} 