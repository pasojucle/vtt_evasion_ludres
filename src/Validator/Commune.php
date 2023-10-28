<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Commune extends Constraint
{
    public $message = 'Séléctionner une commune';
}
