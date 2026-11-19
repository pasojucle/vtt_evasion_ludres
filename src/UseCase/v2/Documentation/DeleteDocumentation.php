<?php

declare(strict_types=1);

namespace App\UseCase\v2\Documentation;

use App\Entity\Documentation;
use App\Repository\Interface\DocumentationRepositoryInterface;
use App\Service\OrderByService;

class DeleteDocumentation
{
    public function __construct(
        private DocumentationRepositoryInterface $documentationRepository,
        private OrderByService $orderByService,
    ) {
    }

    public function __invoke(Documentation $documentation): void
    {
        $documentations = $this->documentationRepository->findAllAsc();
        $this->orderByService->resetOrders($documentations);
        
        $this->documentationRepository->remove($documentation);
    }
}
