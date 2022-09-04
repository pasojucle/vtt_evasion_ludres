<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NotEmptyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof NotEmpty) {
            throw new UnexpectedTypeException($constraint, NotEmpty::class);
        }
        
        if (empty(trim($value))) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
