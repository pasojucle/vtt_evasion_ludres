<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\CommuneRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PostalCodeValidator extends ConstraintValidator
{
    public function __construct(
        private CommuneRepository $communeRepository
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof PostalCode) {
            throw new UnexpectedTypeException($constraint, PostalCode::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!preg_match('#^\d{5}$#', $value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }

        $communes = $this->communeRepository->findByPostalCode($value);
        if (empty($communes)) {
            $this->context->buildViolation($constraint->unknown)
                ->addViolation()
            ;
        }
    }
}
