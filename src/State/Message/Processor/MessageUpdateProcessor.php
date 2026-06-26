<?php

declare(strict_types=1);

namespace App\State\Message\Processor;

use App\Dto\State\ProcessorResult;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class MessageUpdateProcessor implements DialogProcessorInterface
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
            messageKey: 'message.flash.success.Message',
            flashType: 'success',
        );
    }
}
