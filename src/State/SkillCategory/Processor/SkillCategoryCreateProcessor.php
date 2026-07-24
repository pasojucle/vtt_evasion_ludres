<?php

declare(strict_types=1);

namespace App\State\SkillCategory\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\SkillCategory;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SkillCategoryCreateProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @implements FormRedirectProcessorInterface<SkillCategory>
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'skillCategory.flash.success.create',
            flashType: 'success',
        );
    }
}
