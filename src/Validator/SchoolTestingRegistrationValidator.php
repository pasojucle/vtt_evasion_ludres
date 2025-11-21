<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Licence;
use App\Service\LicenceService;
use App\Service\ParameterService;
use App\Validator\SchoolTestingRegistration;
use DateTime;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SchoolTestingRegistrationValidator extends ConstraintValidator
{
    public function __construct(
        private ParameterService $parameterService,
        private LicenceService $licenceService
    ) {
    }
    
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof SchoolTestingRegistration) {
            throw new UnexpectedTypeException($constraint, SchoolTestingRegistration::class);
        }

        if (is_string($value) && 1 === intval($value)) {
            return;
        }

        $birthDate = null;
        $licenceNumber = null;
        if (is_array($value) && array_key_exists('isTesting', $value) && (bool) $value['isTesting']) {
            $birthDate = DateTime::createFromFormat('Y-m-d', $value['birthDate']);
        }

        if (!$birthDate) {
            $identity = $this->context->getObject()?->getParent()->getData();
            $birthDate = $identity?->getBirthDate();
            $licenceNumber = $identity?->getUser()->getLicenceNumber();
            if ('schoolTestingRegistration' !== $this->context->getObject()?->getName()) {
                return;
            }
        }

        if (!$birthDate) {
            return;
        }

        $category = $this->licenceService->getCategoryByBirthDate($birthDate);
        if (LicenceCategoryEnum::SCHOOL !== $category) {
            return;
        }

        $schoolTestingRegistration = $this->parameterService->getSchoolTestingRegistration();
        if (!$schoolTestingRegistration['value'] && !$licenceNumber) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ message }}', strip_tags(html_entity_decode($schoolTestingRegistration['message'])))
                ->addViolation()
            ;
        }
    }
}
