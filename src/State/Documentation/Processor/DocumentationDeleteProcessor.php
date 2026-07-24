<?php

declare(strict_types=1);

namespace App\State\Documentation\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\Documentation;
use App\Repository\DocumentationRepository;
use App\Service\OrderByService;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class DocumentationDeleteProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DocumentationRepository $documentationRepository,
        private OrderByService $orderByService,
    ) {
    }

    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        /** @var Documentation $entity*/
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $documentations = $this->documentationRepository->findAll();
        $this->orderByService->resetOrders($documentations);

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'documentation.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
