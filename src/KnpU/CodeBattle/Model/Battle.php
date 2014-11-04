<?php

namespace KnpU\CodeBattle\Model;

use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *      "programmer",
 *      href = @Hateoas\Route(
 *          "api_programmers_show",
 *          parameters = { "nickname" = "expr(object.programmer.nickname)" }
 *      )
 * )
 */
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
}
