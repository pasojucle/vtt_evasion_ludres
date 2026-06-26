<?php

declare(strict_types=1);

namespace App\State\Product\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Product;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProductToggleProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }
    
    /**
    * @implements DialogProcessorInterface<Product>
    */
    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        $entity->setDisabled(!$entity->isDisabled());
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'product.flash.success.disable',
            flashType: 'success',
        );
    }
}
