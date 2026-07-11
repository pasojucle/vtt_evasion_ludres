<?php

declare(strict_types=1);

namespace App\State\SecondHandCategory\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\SecondHandCategory;
use App\Service\SoftDeleteService;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SecondHandCategoryRestoreProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    /**
     * @param SecondHandCategory $entity
     */
    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        $this->softDeleteService->restore($entity);
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            messageKey: 'category.flash.success.restore',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
