<?php

declare(strict_types=1);

namespace App\State\Link\Processor;

use App\Entity\Link;
use App\Repository\LinkRepository;
use App\Service\OrderByService;
use Doctrine\ORM\EntityManagerInterface;

class LinkDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LinkRepository $linkRepository,
        private OrderByService $orderByService,
    ) {}

    public function process(Link $entity): int
    {
        $position = $entity->getPosition();

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $links = $this->linkRepository->findByPosition($position);
        $this->orderByService->ResetOrders($links);

        return $position;
    }
}