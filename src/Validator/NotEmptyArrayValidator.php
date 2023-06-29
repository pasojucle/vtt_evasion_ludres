<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NotEmptyArrayValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof NotEmptyArray) {
            throw new UnexpectedTypeException($constraint, NotEmptyArray::class);
        }

        $options = $this->context->getObject()->getConfig()->getOptions();

        if (empty($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ name }}', $options['label'])
                ->addViolation()
                
            ;
        }
    }
}
