<?php

declare(strict_types=1);

namespace App\State\Parameter\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\State\Interface\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class ParameterUpdateProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'Parameter.flash.success.Parameter',
            flashType: 'success',
        );
    }
}
