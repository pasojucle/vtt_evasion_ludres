<?php

declare(strict_types=1);

namespace App\State\Notification\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\Notification;
use App\State\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class NotificationToggleProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        /** @var Notification $entity */
        $entity->setIsDisabled(!$entity->isDisabled());
        
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            messageKey: 'notification.flash.success.toggle',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
