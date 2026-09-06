<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\SessionRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SessionUniqueMemberValidator extends ConstraintValidator
{
    public function __construct(
        private SessionRepository $sessionRepository,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof SessionUniqueMember) {
            throw new UnexpectedTypeException($constraint, SessionUniqueMember::class);
        }

        $data = $this->context->getRoot()->getData();
        if (null === $data->user) {
            return;
        }

        if ($this->sessionRepository->findOneByUserAndActivity($data->user, $data->cluster->getBikeRide())) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ fullName }}', $value->getIdentity()->getFullName())
                ->setParameter('{{ cluster }}', $data->cluster->getTitle())
                ->addViolation()
            ;
        }
    }
}
