<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Licence;
use App\Service\LicenceService;
use App\Service\ParameterService;
use App\Validator\SchoolTestingRegistration;
use App\ViewModel\UserPresenter;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SchoolTestingRegistrationValidator extends ConstraintValidator
{
    public function __construct(private ParameterService $parameterService, private UserPresenter $presenter, private LicenceService $licenceService)
    {
    }
    
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof SchoolTestingRegistration) {
            throw new UnexpectedTypeException($constraint, SchoolTestingRegistration::class);
        }

        $user = $this->context->getRoot()->getData();
        $category = $this->licenceService->getCategoryByBirthDate($this->context->getObject()?->getParent()->getData()->getBirthDate());
        $this->presenter->present($user);
  
        if ('schoolTestingRegistration' !== $this->context->getObject()?->getName() || Licence::CATEGORY_MINOR !== $category) {
            return;
        }

        $schoolTestingRegistration = $this->parameterService->getSchoolTestingRegistration($this->presenter->viewModel());

        if (!$schoolTestingRegistration['value'] && !$user->getLicenceNumber()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ message }}', strip_tags(html_entity_decode($schoolTestingRegistration['message'])))
                ->addViolation()
            ;
        }
    }
}
