<?php

declare(strict_types = 1);

namespace App\Controller;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseController
{
    private static ValidatorInterface $_validator;

    /**
     * @return ValidatorInterface
     */
    public static function getValidator(): ValidatorInterface
    {
        if (isset(static::$_validator)) {
            return static::$_validator;
        }

        $validatorBuilder = Validation::createValidatorBuilder();
        $validatorBuilder->enableAnnotationMapping();

        return static::$_validator = $validatorBuilder->getValidator();
    }
}
