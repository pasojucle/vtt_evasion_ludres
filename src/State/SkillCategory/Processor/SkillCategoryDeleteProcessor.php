<?php

declare(strict_types=1);

namespace App\State\SkillCategory\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\SkillCategory;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SkillCategoryDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        /** @var SkillCategory $entity */
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    
        return new ProcessorResult(
            success: true,
            messageKey: 'skill_category.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
