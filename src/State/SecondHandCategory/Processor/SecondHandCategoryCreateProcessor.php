<?php

declare(strict_types=1);

namespace App\State\SecondHandCategory\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\SecondHandCategory;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SecondHandCategoryCreateProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @implements DialogProcessorInterface<SecondHandCategory>
     */
    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'SecondHandCategory.flash.success.Update',
            flashType: 'success',
        );
    }
}
