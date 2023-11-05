<?php

declare(strict_types=1);

namespace App\UseCase\RegistrationStep;

use App\Repository\RegistrationStepRepository;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class EditRegistrationStep
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UploadService $uploadService,
        private RegistrationStepRepository $registrationStepRepository,
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
            dump($step);
            if (null === $step->getOrderBy()) {
                $order = $this->registrationStepRepository->findNexOrderByGroup($step->getRegistrationStepGroup());
                $step->setOrderBy($order);
            }
        }
        $this->entityManager->persist($step);
        $this->entityManager->flush();
    }
}
