<?php

declare(strict_types=1);

namespace App\Mapper\Identity;

use App\Service\FileLocation\DefaultFileLocation;
use App\Service\FileLocation\IdentityFileLocation;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class PassportPhotoMapper
{
        public function __construct(
        private IdentityFileLocation $identityFileLocation,
        private DefaultFileLocation $defaultFileLocation,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }
    public function mapToView(?string $filename): string
    {
        [$directoy, $filename] = $filename
            ? [$this->identityFileLocation->getBaseDirectoryName(), $filename]
            : [$this->defaultFileLocation->getBaseDirectoryName(), 'camera.jpg'];

        return $this->urlGenerator->generate('get_data_file', [
            'directory' => $directoy,
            'filename' => $filename,
        ]);
    }
}