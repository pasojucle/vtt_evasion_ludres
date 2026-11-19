<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\RegistrationStep;

interface RegistrationStepRepositoryInterface
{
    public function save(RegistrationStep $registrationStep, bool $flush = true): void;

    public function remove(RegistrationStep $registrationStep, bool $flush = true): void;

    public function findByGroup(int $group): array;
}
