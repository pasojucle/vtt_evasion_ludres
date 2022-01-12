<?php
namespace App\Validator;

use DateTime;
use DateInterval;
use App\Validator\Phone;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PhoneValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Phone) {
            throw new UnexpectedTypeException($constraint, Phone::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!preg_match('#^\d{10}$#', $value)) {
            $this->context->buildViolation($constraint->message)
            ->addViolation();
        }
    }
}