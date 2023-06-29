<?php

declare(strict_types=1);

namespace App\Validator;

use App\Validator\CKEditorBlank;
use DateInterval;
use DateTime;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CKEditorBlankValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CKEditorBlank) {
            throw new UnexpectedTypeException($constraint, CKEditorBlank::class);
        }


        // if (null === $value ) {
        //     return;
        // }
        if ('' === $value || empty($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
