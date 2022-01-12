<?php
namespace App\Validator;

use App\Validator\PostalCode;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PostalCodeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PostalCode) {
            throw new UnexpectedTypeException($constraint, PostalCode::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!preg_match('#^\d{5}$#', $value)) {
            $this->context->buildViolation($constraint->message)
            ->addViolation();
        }
    }
}