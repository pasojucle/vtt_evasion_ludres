<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class BirthDate extends Constraint
{
    public $message = 'La date de naissance est invalide.';
}
