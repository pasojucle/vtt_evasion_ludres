<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PasswordValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Password) {
            throw new UnexpectedTypeException($constraint, Password::class);
        }

        if (is_string($value) && 6 > mb_strlen($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }

        if (is_array($value) && $value['first'] !== $value['second']) {
            $this->context->buildViolation($constraint->messageRepeat)
                ->addViolation()
            ;
        }
    }
}
