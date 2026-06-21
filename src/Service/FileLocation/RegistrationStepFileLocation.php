<?php

declare(strict_types=1);

namespace App\Service\FileLocation;

use App\Entity\RegistrationStep;

class RegistrationStepFileLocation extends AbstractFileLocation
{
    public function supports(string $className): bool
    {
        return $className === RegistrationStep::class;
    }

    public function getBaseDirectoryName(): string
    {
        return 'registration-step';
    }
}
