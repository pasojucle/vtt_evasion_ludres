<?php

declare(strict_types=1);

namespace App\State\SkillCategory\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\SkillCategory;
use App\Service\SoftDeleteService;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SkillCategoryDeleteProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SoftDeleteService $softDeleteService
    ) {
    }

    /**
     * @param SkillCategory $entity
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        $this->softDeleteService->softDelete($entity);
        $this->entityManager->flush();
    
        return new RedirectProcessorResult(
            success: true,
            messageKey: 'skill_category.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
