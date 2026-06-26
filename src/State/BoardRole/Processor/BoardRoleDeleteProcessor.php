<?php

declare(strict_types=1);

namespace App\State\BoardRole\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\BoardRole;
use App\Repository\BoardRoleRepository;
use App\Repository\MemberRepository;
use App\Service\OrderByService;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class BoardRoleDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MemberRepository $memberRepository,
        private BoardRoleRepository $boardRoleRepository,
        private OrderByService $orderByService,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        /** @var BoardRole $entity */
        $this->memberRepository->removeBoardRole($entity);
        
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $boardRoles = $this->boardRoleRepository->findAllOrdered();
        $this->orderByService->resetOrders($boardRoles);

        return new ProcessorResult(
            success: true,
            messageKey: 'board_role.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
