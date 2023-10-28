<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Commune;
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

        list($postalCode, $communeId) = is_array($value) ? [$value['postalCode'], $value['commune']] : [$value, null];
        if (!preg_match('#^\d{5}$#', $postalCode)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }

        $communes = $this->communeRepository->findByPostalCode($postalCode);
        if (empty($communes)) {
            $this->context->buildViolation($constraint->unknown)
                ->addViolation()
            ;
        }

        if (null === $communeId) {
            return;
        }
 
        if (!empty($communes) && '' === $communeId) {
            $this->context->buildViolation($constraint->emptyCommune)
                ->addViolation()
            ;
        }
    }
}
