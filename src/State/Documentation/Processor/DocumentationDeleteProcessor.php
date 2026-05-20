<?php

declare(strict_types=1);

namespace App\State\Documentation\Processor;

use App\Entity\Documentation;
use App\Repository\DocumentationRepository;
use App\Service\OrderByService;
use Doctrine\ORM\EntityManagerInterface;

class DocumentationDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DocumentationRepository $documentationRepository,
        private OrderByService $orderByService,
    ) {}

    public function process(Documentation $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $documentations = $this->documentationRepository->findAll();
        $this->orderByService->ResetOrders($documentations);
    }
}