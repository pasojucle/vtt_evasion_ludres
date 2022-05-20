<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FullNameValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof FullName) {
            throw new UnexpectedTypeException($constraint, FullName::class);
        }
        $data = $this->context->getRoot()->getData();
        
        if (strtolower($value) === strtolower($data['name'])) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
