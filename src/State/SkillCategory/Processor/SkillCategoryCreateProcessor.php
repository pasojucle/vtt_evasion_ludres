<?php

declare(strict_types=1);

namespace App\State\SkillCategory\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\SkillCategory;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SkillCategoryCreateProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @implements HtmlProcessorInterface<SkillCategory>
     */
    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'skillCategory.flash.success.create',
            flashType: 'success',
        );
    }
}
