<?php

declare(strict_types=1);

namespace App\UseCase\Identity;

use App\Entity\Identity;
use App\Service\CommuneService;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class EditIdentity
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UploadService $uploadService,
        private CommuneService $communeService,
    ) {
    }

    public function execute(Request $request, ?Identity $identity, FormInterface $form): void
    {
        //TODO voir si on peu récupérer file depuis data
        dump($form->getData());
        if ($request->files->get('identitiy')) {
            $pictureFile = $request->files->get('identity')[0]['pictureFile'];
            $newFilename = $this->uploadService->uploadFile($pictureFile);
            if (null !== $newFilename) {
                $identity->setPicture($newFilename);
            }
        }
        if ($identity->getBirthCommune()) {
            $this->communeService->addIfNotExists($identity->getBirthCommune());
        };

        $this->entityManager->flush();
    }
}
