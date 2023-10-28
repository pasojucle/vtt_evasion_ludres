<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\CommuneRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CommuneValidator extends ConstraintValidator
{
    public function __construct(
        private CommuneRepository $communeRepository
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Commune) {
            throw new UnexpectedTypeException($constraint, Commune::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        list($postalCode, $communeId) = [$value['postalCode'], $value['commune']];

        $address = $this->context->getObject()?->getParent()->getData();
        $postalCode = $address->getPostalCode();

        $communes = $this->communeRepository->findByPostalCode($postalCode);

        if (!empty($communes) && '' === $communeId) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
