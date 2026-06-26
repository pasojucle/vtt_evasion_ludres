<?php

declare(strict_types=1);

namespace App\Service\FileLocation;

use App\Entity\Documentation;

class DocumentationFileLocation extends AbstractFileLocation
{
    public function supports(string $className): bool
    {
        return $className === Documentation::class;
    }

    public function getBaseDirectoryName(): string
    {
        return 'documentation';
    }
}
