<?php

declare(strict_types=1);

namespace App\State\Summary\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\Summary;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SummaryDeleteProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }
    /**
     * @implements FormRedirectProcessorInterface<Summary>
     */

    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'slideshow.image.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
