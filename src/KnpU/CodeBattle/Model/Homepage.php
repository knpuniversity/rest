<?php

namespace KnpU\CodeBattle\Model;

use Hateoas\Configuration\Annotation as Hateoas;

/**
 * A model to represent the homepage resource
 *
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "api_homepage"
 *      ),
 *      attributes = {"title": "Your API starting point" }
 * )
 */
class Homepage
{
}
