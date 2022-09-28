<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\GroupSequence;
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

        foreach ($violationsList as $violation) {
            $violations[] = $violation->getMessage();
        }

        return $violations;
    }
}
