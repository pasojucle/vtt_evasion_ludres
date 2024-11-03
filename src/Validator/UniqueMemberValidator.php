<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\User;
use App\Repository\IdentityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueMemberValidator extends ConstraintValidator
{
    public function __construct(
        private IdentityRepository $identityRepository,
        private Security $security,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueMember) {
            throw new UnexpectedTypeException($constraint, UniqueMember::class);
        }

        /** @var User $user */
        $user = $this->security->getUser();

        if (null === $value || '' === $value || null !== $user) {
            return;
        }

        if (is_string($value)) {
            $identity = $this->context->getObject()?->getParent()?->getData();
            if (!$identity || 'firstName' !== $this->context->getObject()?->getName()) {
                return;
            }
            $value = ['name' => $identity->getName(), 'firstName' => $identity->getFirstName()];
        }

        $uniqueMember = (isset($value['name']) && isset($value['firstName']))
            ? $this->identityRepository->findOneByNameAndFirstName(trim($value['name']), trim($value['firstName']))
            : null;

        if ($uniqueMember) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ name }}', $value['name'])
                ->setParameter('{{ firstName }}', $value['firstName'])
                ->addViolation()
            ;
        }
    }
}
