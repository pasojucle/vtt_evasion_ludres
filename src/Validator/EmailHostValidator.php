<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EmailHostValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof EmailHost) {
            throw new UnexpectedTypeException($constraint, EmailHost::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (preg_match('#^.+@.+\.ru$#', $value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
