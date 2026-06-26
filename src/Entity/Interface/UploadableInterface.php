<?php

declare(strict_types=1);

namespace App\Entity\Interface;

interface UploadableInterface
{
    public function getFilename(): ?string;
}
