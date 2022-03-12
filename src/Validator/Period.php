<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Period extends Constraint
{
    public $message = 'Période incohérente : La date de la fin et antérieur à celle du début.';
}
