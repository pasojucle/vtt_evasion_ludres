<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotEmpty extends Constraint
{
    public $message = 'Le champs ne doit pas être vide';
}
