<?php

declare(strict_types=1);

namespace App\UseCase\v2\Identity;

use App\Entity\Identity;
use App\Service\CommuneService;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class UpdateIdentity
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UploadService $uploadService,
        private CommuneService $communeService,
    ) {
    }

    public function execute(?Identity $identity, ?UploadedFile $passportPhoto) {
        
        if ($passportPhoto) {
            $newFilename = $this->uploadService->uploadFile($passportPhoto, $identity);
            if (null !== $newFilename) {
                $identity->setFilename($newFilename);
            }
        }
        if ($identity->getBirthCommune()) {
            $this->communeService->addIfNotExists($identity->getBirthCommune());
        };

        $this->entityManager->flush();
    }
}
