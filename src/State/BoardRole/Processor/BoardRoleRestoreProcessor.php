<?php

declare(strict_types=1);

namespace App\State\BoardRole\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\BoardRole;
use App\Service\SoftDeleteService;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class BoardRoleRestoreProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    /**
     * @param BoardRole $entity
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        $this->softDeleteService->restore($entity);
        $this->entityManager->flush();
        ;

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'board_role.flash.success.restore',
        );
    }
}
