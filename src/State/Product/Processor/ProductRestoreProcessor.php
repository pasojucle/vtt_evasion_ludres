<?php

declare(strict_types=1);

namespace App\State\Product\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\Product;
use App\Service\SoftDeleteService;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProductRestoreProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    /**
     * @param Product $entity
     */
    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        $this->softDeleteService->restore($entity);
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            messageKey: 'documentation.flash.success.restore',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
