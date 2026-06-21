<?php

declare(strict_types=1);

namespace App\Service\FileLocation;

use App\Model\TextEditorUpload;

class TextEditorFileLocation extends AbstractFileLocation
{
    public function supports(string $className): bool
    {
        return $className === TextEditorUpload::class;
    }

    public function getBaseDirectoryName(): string
    {
        return 'upload';
    }
}