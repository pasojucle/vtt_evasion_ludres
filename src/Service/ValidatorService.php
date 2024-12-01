<?php

declare(strict_types=1);

namespace App\Service;

use App\Validator\SchoolTestingRegistration;
use App\Validator\UniqueMember;
use ReflectionClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorService
{
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    public function ValidateToArray(mixed $value, Constraint|array $constraints = null, string|GroupSequence|array $groups = null): array
    {
        $violationsList = $this->validator->validate($value, $constraints, $groups);

        $violations = [];

        /** @var ConstraintViolation $violation */
        foreach ($violationsList as $violation) {
            $constraint = new ReflectionClass($violation->getConstraint());
            preg_match('#^symfony|app#i', $constraint->getNamespaceName(), $nameSpace);

            $violations[] = [
                'message' => $violation->getMessage(),
                'constraint' => sprintf('%s-%s', strtolower($nameSpace[0]), $constraint->getShortName()),
            ];
        }

        return $violations;
    }
}
