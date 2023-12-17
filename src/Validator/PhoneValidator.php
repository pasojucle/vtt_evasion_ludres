<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PhoneValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Phone) {
            throw new UnexpectedTypeException($constraint, Phone::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (is_array($value)) {
            if (array_key_exists('mobile', $value) && array_key_exists('emergencyPhone', $value) && $value['emergencyPhone'] === $value['mobile']) {
                $this->context->buildViolation($constraint->nonUnique)
                ->addViolation()
            ;
            }
        }
        
        if (is_string($value) && !preg_match('#^\d{2}\s\d{2}\s\d{2}\s\d{2}\s\d{2}$#', $value)) {
            $this->context->buildViolation($constraint->format)
                ->addViolation()
            ;
        }
    }
}
