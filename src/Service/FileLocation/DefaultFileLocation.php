<?php

declare(strict_types=1);

namespace App\Service\FileLocation;


class DefaultFileLocation extends AbstractFileLocation
{
    public function supports(string $className): bool
    {
        return false;
    }

    public function getBaseDirectoryName(): string
    {
        return 'default';
    }
}
