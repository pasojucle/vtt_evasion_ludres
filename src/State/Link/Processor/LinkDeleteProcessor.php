<?php

declare(strict_types=1);

namespace App\State\Link\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Link;
use App\Repository\LinkRepository;
use App\Service\OrderByService;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LinkDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LinkRepository $linkRepository,
        private OrderByService $orderByService,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        /** @var Link $entity */
        $position = $entity->getPosition();

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $links = $this->linkRepository->findByPosition($position);
        $this->orderByService->resetOrders($links);

    
        return new ProcessorResult(
            success: true,
            messageKey: 'link.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
