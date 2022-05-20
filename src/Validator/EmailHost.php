<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EmailHost extends Constraint
{
    public $message = 'Adresse mail erronnée.';
}
