<?php

declare(strict_types=1);

namespace App\State\BoardRole\Processor;

use App\Entity\BoardRole;
use App\Repository\BoardRoleRepository;
use App\Repository\MemberRepository;
use App\Service\OrderByService;
use Doctrine\ORM\EntityManagerInterface;

class BoardRoleDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MemberRepository $memberRepository,
        private BoardRoleRepository $boardRoleRepository,
        private OrderByService $orderByService,
    ) {}

    public function process(BoardRole $entity): void
    {
        $this->memberRepository->removeBoardRole($entity);
        
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $boardRoles = $this->boardRoleRepository->findAllOrdered();

        $this->orderByService->ResetOrders($boardRoles);
    }
}