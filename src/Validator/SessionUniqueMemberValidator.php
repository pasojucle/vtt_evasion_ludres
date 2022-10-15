<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\SessionRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SessionUniqueMemberValidator extends ConstraintValidator
{
    public function __construct(
        private SessionRepository $sessionRepository,
        private RequestStack $request
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof SessionUniqueMember) {
            throw new UnexpectedTypeException($constraint, SessionUniqueMember::class);
        }

        $session = $this->context->getRoot()->getData();
        $clusters = unserialize($this->request->getSession()->get('admin_session_add_clusters'));

        dump($clusters, $session->getUser());

        dump($this->sessionRepository->findByUserAndClusters($session->getUser(), $clusters));
        if ($this->sessionRepository->findByUserAndClusters($session->getUser(), $clusters)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ name }}', $value['name'])
                ->setParameter('{{ firstName }}', $value['firstName'])
                ->addViolation()
            ;
        }
    }
}
