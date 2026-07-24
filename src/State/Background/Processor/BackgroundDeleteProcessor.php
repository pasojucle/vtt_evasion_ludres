<?php

declare(strict_types=1);

namespace App\State\Background\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\Background;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class BackgroundDeleteProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        /** @var Background $entity */
        $this->entityManager->remove($entity);

        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'background.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
