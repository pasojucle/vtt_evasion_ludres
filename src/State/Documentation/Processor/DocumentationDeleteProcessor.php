<?php

declare(strict_types=1);

namespace App\State\Documentation\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\Documentation;
use App\Repository\DocumentationRepository;
use App\Service\OrderByService;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class DocumentationDeleteProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DocumentationRepository $documentationRepository,
        private OrderByService $orderByService,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        /** @var Documentation $entity*/
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $documentations = $this->documentationRepository->findAll();
        $this->orderByService->resetOrders($documentations);

        return new HtmlProcessorResult(
            success: true,
            messageKey: 'documentation.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
