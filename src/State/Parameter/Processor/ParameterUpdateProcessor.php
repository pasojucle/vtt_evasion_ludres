<?php

declare(strict_types=1);

namespace App\State\Parameter\Processor;

use App\Dto\State\ProcessorResult;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class ParameterUpdateProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'Parameter.flash.success.Parameter',
            flashType: 'success',
        );
    }
}
