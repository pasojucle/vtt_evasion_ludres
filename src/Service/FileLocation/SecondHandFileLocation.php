<?php

declare(strict_types=1);

namespace App\Service\FileLocation;

use App\Entity\SecondHandImage;

class SecondHandFileLocation extends AbstractFileLocation
{
    public function supports(string $className): bool
    {
        return $className === SecondHandImage::class;
    }

    public function getBaseDirectoryName(): string
    {
        return 'second_hand';
    }
}
