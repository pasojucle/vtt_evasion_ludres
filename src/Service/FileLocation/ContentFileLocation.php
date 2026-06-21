<?php

declare(strict_types=1);

namespace App\Service\FileLocation;


use App\Entity\Content;

class ContentFileLocation extends AbstractFileLocation
{
    public function supports(string $className): bool
    {
        return $className === Content::class;
    }

    public function getBaseDirectoryName(): string
    {
        return 'content';
    }
}
