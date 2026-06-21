<?php

declare(strict_types=1);

namespace App\Service\FileLocation;

use App\Entity\Background;

class BackgroundFileLocation extends AbstractFileLocation
{
    public function supports(string $className): bool
    {
        return $className === Background::class;
    }

    public function getBaseDirectoryName(): string
    {
        return 'background';
    }
}
