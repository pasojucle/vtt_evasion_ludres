<?php

declare(strict_types=1);

namespace App\State\Link\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\Link;
use App\Repository\LinkRepository;
use App\Service\OrderByService;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LinkDeleteProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LinkRepository $linkRepository,
        private OrderByService $orderByService,
    ) {
    }

    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        /** @var Link $entity */
        $position = $entity->getPosition();

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $links = $this->linkRepository->findByPosition($position);
        $this->orderByService->resetOrders($links);

    
        return new RedirectProcessorResult(
            success: true,
            messageKey: 'link.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
