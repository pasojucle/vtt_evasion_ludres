<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PostalCode extends Constraint
{
    public $message = 'Le code postal doit comporter 5 chiffres.';

    public $unknown = 'Ce code postal est invalide.';
}
