<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Interface\UploadableInterface;

class TextEditorUpload implements UploadableInterface
{
    public function __construct(
        private ?string $filename = null
    ) {}

    public function getFilename(): ?string
    {
        return $this->filename;
    }
}