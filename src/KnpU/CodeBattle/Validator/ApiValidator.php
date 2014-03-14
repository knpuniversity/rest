<?php

namespace KnpU\CodeBattle\Validator;

use Symfony\Component\Validator\ValidatorInterface;

/**
 * A class that uses Symfony's validator, but flattens things down to a simpler
 * format
 */
class ApiValidator
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate($obj)
    {
        $errors = $this->validator->validate($obj);
        $errorsData = array();

        foreach ($errors as $error) {
            /** @var \Symfony\Component\Validator\ConstraintViolation $error */

            // reduces to just one error per field, that's a decision I'm making
            $errorsData[$error->getPropertyPath()] = $error->getMessage();
        }

        return $errorsData;
    }
} 