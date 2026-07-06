<?php

declare(strict_types=1);

namespace App\State\Message\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\Message;
use App\State\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class MessageDeleteProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        /** @var Message $entity */
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            messageKey: 'message.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
