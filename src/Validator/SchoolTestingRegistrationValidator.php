<?php

declare(strict_types=1);

namespace App\Validator;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Licence;
use App\Service\LicenceService;
use App\Service\ParameterService;
use App\Validator\SchoolTestingRegistration;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SchoolTestingRegistrationValidator extends ConstraintValidator
{
    public function __construct(
        private ParameterService $parameterService,
        private UserDtoTransformer $userDtoTransformer,
        private LicenceService $licenceService
    ) {
    }
    
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof SchoolTestingRegistration) {
            throw new UnexpectedTypeException($constraint, SchoolTestingRegistration::class);
        }

        $identity = $this->context->getObject()?->getParent()->getData();
        $user = $identity->getUser();
        if (!$identity->getBirthDate()) {
            return;
        }
        $category = $this->licenceService->getCategoryByBirthDate($identity->getBirthDate());
  
        if ('schoolTestingRegistration' !== $this->context->getObject()?->getName() || Licence::CATEGORY_MINOR !== $category) {
            return;
        }

        $schoolTestingRegistration = $this->parameterService->getSchoolTestingRegistration($this->userDtoTransformer->fromEntity($user));

        if (!$schoolTestingRegistration['value'] && !$user->getLicenceNumber()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ message }}', strip_tags(html_entity_decode($schoolTestingRegistration['message'])))
                ->addViolation()
            ;
        }
    }
}
