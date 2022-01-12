<?php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PostalCode extends Constraint
{
    public $message = 'Le code postal doit comporter 5 chiffres.';
}