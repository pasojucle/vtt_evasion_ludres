<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SessionUniqueMember extends Constraint
{
    public $message = '{{ name }} {{ firstName }} est déjà inscrit';
}
