<?php

declare(strict_types=1);

namespace App\Service\FileLocation;

use App\Entity\Identity;

class IdentityFileLocation extends AbstractFileLocation
{
    public function supports(string $className): bool
    {
        return $className === Identity::class;
    }

    public function getBaseDirectoryName(): string
    {
        return 'identity';
    }
}
