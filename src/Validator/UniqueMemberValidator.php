<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\IdentityRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueMemberValidator extends ConstraintValidator
{
    public function __construct(
        private IdentityRepository $identityRepository,
        private Security $security
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        if (! $constraint instanceof UniqueMember) {
            throw new UnexpectedTypeException($constraint, UniqueMember::class);
        }

        if (null === $value || '' === $value || is_string($value) || null !== $this->security->getUser()) {
            return;
        }

        $uniqueMember = $this->identityRepository->findByNameAndFirstName($value['name'], $value['firstName']);
        if (! empty($uniqueMember)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ name }}', $value['name'])
                ->setParameter('{{ firstName }}', $value['firstName'])
                ->addViolation()
            ;
        }
    }
}
