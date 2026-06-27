<?php

declare(strict_types=1);

namespace App\State\SecondHandCategory\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\SecondHandCategory;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SecondHandCategoryDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        /** @var SecondHandCategory $entity */
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
