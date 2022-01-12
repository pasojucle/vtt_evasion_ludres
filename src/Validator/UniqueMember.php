<?php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueMember extends Constraint
{
    public $message = 'Un compte avec le nom {{ name }} {{ firstName }} existe déjà';
}