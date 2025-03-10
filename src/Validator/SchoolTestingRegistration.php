<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SchoolTestingRegistration extends Constraint
{
    public $message = '{{ message }}';
}
