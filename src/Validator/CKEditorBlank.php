<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CKEditorBlank extends Constraint
{
    public $message = 'Le contenu de doit pas être vide.';
}
