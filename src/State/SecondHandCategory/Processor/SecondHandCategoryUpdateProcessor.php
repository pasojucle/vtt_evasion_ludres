<?php

declare(strict_types=1);

namespace App\State\SecondHandCategory\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\SecondHandCategory;
use App\State\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SecondHandCategoryUpdateProcessor implements HtmlProcessorInterface
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
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'secondHandCategory.flash.success.Uupdate',
            flashType: 'success',
        );
    }
}
