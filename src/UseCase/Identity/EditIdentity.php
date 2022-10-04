<?php

declare(strict_types=1);

namespace App\UseCase\Identity;

use App\Entity\User;
use App\Service\CommuneService;
use App\Service\IdentityService;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class EditIdentity
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private IdentityService $identityService,
        private UploadService $uploadService,
        private CommuneService $communeService
    ) {
    }

    public function execute(Request $request, ?User $user, FormInterface $form): void
    {
        $identities = $form->getData();
        $this->identityService->setAddress($user);

        if ($request->files->get('identities')) {
            $pictureFile = $request->files->get('identities')['identities'][0]['pictureFile'];
            $newFilename = $this->uploadService->uploadFile($pictureFile);
            if (null !== $newFilename) {
                foreach ($identities as $identity) {
                    if (null === $identity[0]->getKinship()) {
                        $identity[0]->setPicture($newFilename);
                    }
                }
            }
        }

        if ($user->getMemberIdentity()->getBirthCommune()) {
            $this->communeService->addIfNotExists($user->getMemberIdentity()->getBirthCommune());
        };

        $this->entityManager->flush();
    }
}
