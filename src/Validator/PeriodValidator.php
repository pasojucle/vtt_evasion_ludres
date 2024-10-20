<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PeriodValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Period) {
            throw new UnexpectedTypeException($constraint, Period::class);
        }

        $form = $this->context->getObject()->getParent()->getData();
        $startAt = (is_array($form))
            ? $form['startAt']
            : $form->getStartAt();

        if (null === $value || '' === $value) {
            return;
        }
        if ($value < $startAt) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
