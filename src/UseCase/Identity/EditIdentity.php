<?php

namespace App\UseCase\Identity;

use App\Entity\User;
use App\Service\UploadService;
use App\Service\IdentityService;
use App\ViewModel\UserPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class EditIdentity
{
    private UserPresenter $presenter;
    private EntityManagerInterface $entityManager;
    private IdentityService $identityService;
    private UploadService $uploadService;

    public function __construct(
        UserPresenter $presenter,
        EntityManagerInterface $entityManager,
        IdentityService $identityService,
        UploadService $uploadService
    )
    {
        $this->presenter = $presenter;
        $this->entityManager = $entityManager;
        $this->identityService = $identityService;
        $this->uploadService = $uploadService;
    }

    public function execute(Request $request, ?User $user, FormInterface $form): void
    {
        $identity = $form->getData();
        $this->identityService->setAddress($user);
        if ($request->files->get('identity')) {
            $pictureFile = $request->files->get('identity')['pictureFile'];
            $newFilename = $this->uploadService->uploadFile($pictureFile);
            if (null !== $newFilename) {
                $identity->setPicture($newFilename);
            }
        }
        $this->entityManager->flush();
    }
}