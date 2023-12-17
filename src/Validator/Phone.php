<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Phone extends Constraint
{
    public $format = 'Le numéro doit comporter 10 chiffres.';
    public $nonUnique = 'Ce numéro doit être différent du téléphone mobile.';
}
