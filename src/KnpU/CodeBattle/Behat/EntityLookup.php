<?php

namespace KnpU\CodeBattle\Behat;

use KnpU\CodeBattle\Repository\BaseRepository;

/**
 * Class used in ApiFeatureContext::processReplacements to lookup entity ids
 */
class EntityLookup
{
    private $repository;

    private $field;

    public function __construct(BaseRepository $repository, $field)
    {
        $this->repository = $repository;
        $this->field = $field;
    }

    public function __get($value)
    {
        $obj = $this->repository->findOneBy(array($this->field => $value));
        if (!$obj) {
            throw new \Exception(sprintf(
                'Cannot find %s=%s via %s',
                $this->field,
                $value,
                get_class($this->repository)
            ));
        }

        return $obj;
    }
} 