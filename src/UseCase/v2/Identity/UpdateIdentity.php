<?php

declare(strict_types=1);

namespace App\UseCase\v2\Identity;

use App\Entity\Identity;
use App\Service\CommuneService;
use App\Service\UploadService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UpdateIdentity
{
    public function __construct(
        private UploadService $uploadService,
        private CommuneService $communeService,
    ) {
    }

    public function execute(Identity $identity, ?UploadedFile $passportPhoto): void
    {
        if ($passportPhoto) {
            $newFilename = $this->uploadService->uploadFile($passportPhoto, $identity);
            if (null !== $newFilename) {
                $identity->setFilename($newFilename);
            }
        }
        if ($identity->getBirthCommune()) {
            $this->communeService->addIfNotExists($identity->getBirthCommune());
        };
    }
}
