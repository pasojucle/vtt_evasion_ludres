<?php

declare(strict_types=1);

namespace App\UseCase\RegistrationStep;

use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class EditRegistrationStep
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UploadService $uploadService
    ) {
    }

    public function execute(Request $request, FormInterface $form): void
    {
        $step = $form->getData();
        if ($request->files->get('registration_step')) {
            $pdfFile = $request->files->get('registration_step')['pdfFile'];
            $newFilename = $this->uploadService->uploadFile($pdfFile, 'files_directory_path');
            if (null !== $newFilename) {
                $step->setFilename($newFilename);
            }
        }
        $this->entityManager->flush();
    }
}
