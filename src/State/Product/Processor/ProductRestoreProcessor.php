<?php

declare(strict_types=1);

namespace App\State\Product\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\Product;
use App\Service\SoftDeleteService;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProductRestoreProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    /**
     * @param Product $entity
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        $this->softDeleteService->restore($entity);
        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'documentation.flash.success.restore',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
