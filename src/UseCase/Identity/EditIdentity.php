<?php

declare(strict_types=1);

namespace App\UseCase\Identity;

use App\Entity\User;
use App\Service\IdentityService;
use App\Service\UploadService;
use App\ViewModel\UserPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class EditIdentity
{
    public function __construct(
        private UserPresenter $presenter,
        private EntityManagerInterface $entityManager,
        private IdentityService $identityService,
        private UploadService $uploadService
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
        $this->entityManager->flush();
    }
}
