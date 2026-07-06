<?php

declare(strict_types=1);

namespace App\State\Product\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\Product;
use App\State\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProductToggleProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }
    
    /**
    * @implements HtmlProcessorInterface<Product>
    */
    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        $entity->setDisabled(!$entity->isDisabled());
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'product.flash.success.disable',
            flashType: 'success',
        );
    }
}
