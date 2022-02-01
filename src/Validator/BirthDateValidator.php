<?php

declare(strict_types=1);

namespace App\Validator;

use DateInterval;
use DateTime;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class BirthDateValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof BirthDate) {
            throw new UnexpectedTypeException($constraint, BirthDate::class);
        }

        $minLimit = new DateTime();
        $minLimit->sub(new DateInterval('P80Y'));
        $maxLimit = new DateTime();
        $maxLimit->sub(new DateInterval('P5Y'));

        if (null === $value || '' === $value) {
            return;
        }
        if (!($minLimit < $value && $value < $maxLimit)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
