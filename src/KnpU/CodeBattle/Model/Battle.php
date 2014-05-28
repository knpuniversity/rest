<?php

namespace KnpU\CodeBattle\Model;

use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Serializer\ExclusionPolicy("all")
 * @Hateoas\Relation(
 *      "programmer",
 *      href = @Hateoas\Route(
 *          "api_programmers_show",
 *          parameters = { "nickname" = "expr(object.programmer.nickname)" }
 *      ),
 *      embedded = "expr(object.programmer)"
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

    /**
     * @Serializer\Expose
     */
    public $didProgrammerWin = true;

    /**
     * @Serializer\Expose
     */
    public $foughtAt;

    /**
     * @Serializer\Expose
     */
    public $notes;
}
