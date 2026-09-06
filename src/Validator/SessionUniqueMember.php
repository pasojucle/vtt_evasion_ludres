<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SessionUniqueMember extends Constraint
{
    public $message = '{{ fullName }} est déjà inscrit dans le groupe {{ cluster }}';
}
