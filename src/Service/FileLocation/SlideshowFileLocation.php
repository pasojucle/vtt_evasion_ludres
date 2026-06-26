<?php

declare(strict_types=1);

namespace App\Service\FileLocation;

use App\Entity\Interface\UploadableInterface;
use App\Entity\SlideshowImage;

class SlideshowFileLocation extends AbstractFileLocation
{
    public function supports(string $className): bool
    {
        return $className === SlideshowImage::class;
    }

    public function getBaseDirectoryName(): string
    {
        return 'slideshow';
    }

    public function getPath(UploadableInterface $media): ?string
    {
        /** @var SlideshowImage $media */
        if (null === $media->getFilename()) {
            return null;
        }

        $slideShowDirectory = $media->getDirectory();
        if (null === $slideShowDirectory) {
            return null;
        }

        return $this->fileService->join(
            $this->projectDir,
            'data',
            $this->getBaseDirectoryName(),
            (string) $media->getDirectory()->getId(),
            $media->getFilename()
        );
    }

    public function getDirectory(?UploadableInterface $media = null): string
    {
        /** @var ?SlideshowImage $media */
        return $this->fileService->join(
            $this->projectDir,
            'data',
            $this->getBaseDirectoryName(),
            (string) ($media?->getDirectory()->getId() ?? 'tmp')
        );
    }
}
