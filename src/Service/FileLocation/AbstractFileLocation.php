<?php

declare(strict_types=1);

namespace App\Service\FileLocation;

use App\Entity\Interface\UploadableInterface;
use App\Service\FileService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

abstract class AbstractFileLocation implements FileLocationInterface
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        protected string $projectDir,
        protected FileService $fileService
    ) {}

    abstract public function getBaseDirectoryName(): string;
    abstract public function supports(string $className): bool;

    public function getPath(UploadableInterface $media): ?string
    {
        if (null === $media->getFilename()) {
            return null;
        }

        return $this->fileService->join($this->projectDir, 'data', $this->getBaseDirectoryName(), $media->getFilename());
    }

    public function getDirectory(?UploadableInterface $media = null): string
    {
        return $this->fileService->join($this->projectDir, 'data', $this->getBaseDirectoryName());
    }
}