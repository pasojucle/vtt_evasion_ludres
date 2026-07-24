<?php

declare(strict_types=1);

namespace App\State\Message\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\Message;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class MessageDeleteProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        /** @var Message $entity */
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'message.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
