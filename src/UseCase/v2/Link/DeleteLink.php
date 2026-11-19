<?php

declare(strict_types=1);

namespace App\UseCase\v2\Link;

use App\Entity\Link;
use App\Repository\Interface\LinkRepositoryInterface;
use App\Service\OrderByService;

class DeleteLink
{
    public function __construct(
        private LinkRepositoryInterface $linkRepository,
        private OrderByService $orderByService,
    ) {
    }

    public function __invoke(Link $link): void
    {
        $position = $link->getPosition();

        $links = $this->linkRepository->findByPosition($position);
        $this->orderByService->resetOrders($links);
        
        $this->linkRepository->save($link);
    }
}
