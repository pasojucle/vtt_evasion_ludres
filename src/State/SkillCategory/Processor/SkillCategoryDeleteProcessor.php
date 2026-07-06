<?php

declare(strict_types=1);

namespace App\State\SkillCategory\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\SkillCategory;
use App\Service\SoftDeleteService;
use App\State\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SkillCategoryDeleteProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SoftDeleteService $softDeleteService
    ) {
    }

    /**
     * @param SkillCategory $entity
     */
    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        $this->softDeleteService->softDelete($entity);
        $this->entityManager->flush();
    
        return new HtmlProcessorResult(
            success: true,
            messageKey: 'skill_category.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
