<?php

namespace KnpU\CodeBattle\Model;

use JMS\Serializer\Annotation as Serializer;

class Battle
{
    /* All public properties are persisted */
    public $id;

    /**
     * @var Programmer
     */
    public $programmer;

    /**
     * @var Project
     */
    public $project;

    public $didProgrammerWin;

    public $foughtAt;

    public $notes;

    /**
     * @Serializer\VirtualProperty()
     */
    public function getProgrammerUri()
    {
        return '/api/programmers/'.$this->programmer->nickname;
    }
}
