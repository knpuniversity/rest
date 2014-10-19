<?php

namespace KnpU\CodeBattle\Model;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\ExclusionPolicy("all")
 */
class Programmer
{

    /* All public properties are persisted */
    public $id;

    /**
     * @Assert\NotBlank(message="Please enter a clever nickname")
     * @Serializer\Expose
     */
    public $nickname;

    /**
     * Number of an avatar, from 1-6
     *
     * @var integer
     * @Serializer\Expose
     */
    public $avatarNumber;

    /**
     * * @Serializer\Expose
     */
    public $tagLine;

    public $userId;

    /**
     * @Serializer\Expose
     */
    public $powerLevel = 0;

    public function __construct($nickname = null, $avatarNumber = null)
    {
        $this->nickname = $nickname;
        $this->avatarNumber = $avatarNumber;
    }


}
