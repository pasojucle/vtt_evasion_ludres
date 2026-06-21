<?php

declare(strict_types=1);

namespace App\Service\FileLocation;

use App\Entity\Interface\UploadableInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.file_location')]
interface FileLocationInterface
{
    public function supports(string $className): bool;

    public function getPath(UploadableInterface $media): ?string;

    public function getBaseDirectoryName(): string;
}
