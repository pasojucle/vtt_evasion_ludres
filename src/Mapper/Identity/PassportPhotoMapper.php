<?php

declare(strict_types=1);

namespace App\Mapper\Identity;

use App\Service\FileLocation\DefaultFileLocation;
use App\Service\FileLocation\IdentityFileLocation;
use App\Service\FileService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PassportPhotoMapper
{
    public function __construct(
        private IdentityFileLocation $identityFileLocation,
        private DefaultFileLocation $defaultFileLocation,
        private UrlGeneratorInterface $urlGenerator,
        private Filesystem $filesystem,
        private FileService $fileService,
    ) {
    }
    public function mapToView(?string $filename): string
    {
        [$directoy, $filename] = $filename
            ? $this->fileLocation($filename)
            : $this->defaultFileLocation();

        return $this->urlGenerator->generate('get_data_file', [
            'directory' => $directoy,
            'filename' => $filename,
        ]);
    }

    private function defaultFileLocation(): array
    {
        return [$this->defaultFileLocation->getBaseDirectoryName(), 'camera.jpg'];
    }

    private function fileLocation(?string $filename): array
    {
        $directoy = $this->identityFileLocation->getBaseDirectoryName();
        $path = $this->fileService->join($directoy, $filename);

        if ($this->filesystem->exists($path)) {
            return [$directoy, $filename];
        }

        return $this->defaultFileLocation();
    }
}
