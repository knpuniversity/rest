<?php

namespace KnpU\CodeBattle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig_SimpleFunction;

class BattleExtension extends \Twig_Extension_Core
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('asset', array($this, 'getAssetPath')),
        );
    }

    public function getAssetPath($path)
    {
        return $this->requestStack->getCurrentRequest()->getBasePath().'/'.$path;
    }
}