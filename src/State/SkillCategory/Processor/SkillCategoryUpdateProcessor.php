<?php

declare(strict_types=1);

namespace App\State\SkillCategory\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\SkillCategory;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SkillCategoryUpdateProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @implements DialogProcessorInterface<SkillCategory>
     */
    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'SkillCategory.flash.success.Update',
            flashType: 'success',
        );
    }
}
