<?php

declare(strict_types=1);

namespace App\State\Skill\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\Skill;
use App\Service\SoftDeleteService;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SkillDeleteProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SoftDeleteService $softDeleteService
    ) {
    }

    /**
     * @param Skill $entity
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): HtmlProcessorResult
    {
        $this->softDeleteService->softDelete($entity);
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            messageKey: 'level.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success',
        );
    }
}
