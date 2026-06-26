<?php

declare(strict_types=1);

namespace App\State\Category\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Category;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class CategoryDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        /** @var Category $entity */
        $entity->setDeleted(true);
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            messageKey: 'category.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
