<?php

declare(strict_types=1);

namespace App\State\Level\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\Level;
use App\Service\SoftDeleteService;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LevelRestoreProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    /**
     * @param Level $entity
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        $this->softDeleteService->restore($entity);
        $this->entityManager->flush();
        ;

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'level.flash.success.restore',
        );
    }
}
