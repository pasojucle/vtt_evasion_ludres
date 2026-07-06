<?php

declare(strict_types=1);

namespace App\State\SecondHandCategory\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\SecondHandCategory;
use App\State\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SecondHandCategoryCreateProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @implements HtmlProcessorInterface<SecondHandCategory>
     */
    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'secondHandCategory.flash.success.create',
            flashType: 'success',
        );
    }
}
