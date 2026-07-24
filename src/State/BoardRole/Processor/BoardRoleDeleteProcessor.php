<?php

declare(strict_types=1);

namespace App\State\BoardRole\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\BoardRole;
use App\Repository\BoardRoleRepository;
use App\Service\OrderByService;
use App\Service\SoftDeleteService;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class BoardRoleDeleteProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BoardRoleRepository $boardRoleRepository,
        private OrderByService $orderByService,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    /**
     * @param BoardRole $entity
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        $this->softDeleteService->softDelete($entity);
        $this->entityManager->flush();

        $boardRoles = $this->boardRoleRepository->findAllOrdered();
        $this->orderByService->resetOrders($boardRoles);
        $this->orderByService->setNewOrders($entity, $boardRoles, count($boardRoles));

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'board_role.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
