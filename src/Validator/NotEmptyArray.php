<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotEmptyArray extends Constraint
{
    public $message = 'Le champs {{ name }} ne doit pas être vide';
}
