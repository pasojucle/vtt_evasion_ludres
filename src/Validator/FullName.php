<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class FullName extends Constraint
{
    public $message = 'Le nom et prénom doivent être différents.';
}
