<?php

declare(strict_types=1);

namespace App\State\Message\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\State\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class MessageUpdateProcessor implements HtmlProcessorInterface
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
            messageKey: 'message.flash.success.Message',
            flashType: 'success',
        );
    }
}
