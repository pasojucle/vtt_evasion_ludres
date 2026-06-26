<?php

declare(strict_types=1);

namespace App\State\Product\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Product;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProductDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        /** @var Product $entity */
        $entity->setDeleted(true);
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            messageKey: 'documentation.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
