<?php

declare(strict_types=1);

namespace App\State\Message\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Message;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class MessageDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        /** @var Message $entity */
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            messageKey: 'message.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
