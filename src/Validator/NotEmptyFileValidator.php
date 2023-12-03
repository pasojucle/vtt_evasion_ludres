<?php

namespace App\Validator;

use App\Validator\NotEmptyFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NotEmptyFileValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var NotEmptyFile $constraint */
        if (!$constraint instanceof NotEmptyFile) {
            throw new UnexpectedTypeException($constraint, NotEmptyFile::class);
        }

        if ($value instanceof UploadedFile) {
            return;
        }

        $form = $this->context->getObject()->getParent()->getData();
        if (empty($form->getFilename())) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
