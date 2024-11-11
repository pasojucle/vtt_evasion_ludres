<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class SlideshowService
{
    public function __construct(
        private readonly ProjectDirService $projectDir,
        private readonly ParameterService $parameterService,
        private readonly FileService $fileService,
    ) {
    }

    public function getSize(): int
    {
        $finder = new Finder();
        
        $finder->files()->in($this->projectDir->path('slideshow'));
        $slideshowSize = 0;
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $slideshowSize += $file->getSize();
        }

        return $slideshowSize;
    }

    public function getMaxSize(): string
    {
        return $this->parameterService->getParameterByName('SLIDESHOW_MAX_DISK_SIZE');
    }

    public function isFull(): bool
    {
        return $this->fileService->humanToBytes($this->getMaxSize()) <= $this->getSize();
    }

    public function getSpace(): array
    {
        $size = $this->getSize();
        $maxHuman = $this->getMaxSize();
        $max = $this->fileService->humanToBytes($maxHuman);
        $value = round($size / $max * 100, 1);

        return [
            'text' => sprintf('%s libres sur %s', $this->fileService->bytesToHuman($max - $size), $maxHuman),
            'value' => $value,
            'color' => ($value < 85) ? 'progress-success' : 'progress-danger',
        ];
    }
}
