<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Password extends Constraint
{
    public $message = 'Le mot passe doit avoir 6 caractères minimum.';

    public $messageRepeat = 'Les mots passe ne correspondent pas.';
}
