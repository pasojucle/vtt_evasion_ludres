<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RangeAgeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof RangeAge) {
            throw new UnexpectedTypeException($constraint, RangeAge::class);
        }

        $form = $this->context->getObject()->getParent()->getData();

        $minAge = (is_array($form))
            ? $form['minAge']
            : $form->getMinAge();

        if (null === $value || '' === $value) {
            return;
        }
        if ((int) $value <= (int) $minAge) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
