<?php

namespace App\Validator;

use App\Validator\LicenceNumber;
use App\Repository\LicenceRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Intl\Exception\UnexpectedTypeException;

final class LicenceNumberValidator extends ConstraintValidator
{
    public function __construct(
        private readonly LicenceRepository $licenceRepository,
    )
    {
    }
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var LicenceNumber $constraint */
        if (!$constraint instanceof LicenceNumber) {
            throw new UnexpectedTypeException($constraint, LicenceNumber::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $licence = $this->licenceRepository->find($value);


        if (!$licence) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation()
            ;
        }
    }
}
