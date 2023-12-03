<?php

declare(strict_types=1);

namespace App\UseCase\Documentation;

use App\Entity\Documentation;
use App\Repository\DocumentationRepository;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class EditDocumentation
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DocumentationRepository $documentationRepository,
        private UploadService $uploadService,
    ) {
    }

    public function execute(FormInterface $form, Request $request, bool $persist = false): Documentation
    {
        $documentation = $form->getData();
        if ($request->files->get('documentation')) {
            $file = $request->files->get('documentation')['file'];
            $documentation->setFileName($this->uploadService->uploadFile($file, 'documentation'));
        }
        if (null === $documentation->getOrderBy()) {
            $order = $this->documentationRepository->findNexOrder();
            $documentation->setOrderBy($order);
        }

        if ($persist) {
            $this->entityManager->persist($documentation);
        }
        
        $this->entityManager->flush();

        return $documentation;
    }
}
