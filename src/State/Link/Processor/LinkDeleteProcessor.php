<?php

declare(strict_types=1);

namespace App\State\Link\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\Link;
use App\Repository\LinkRepository;
use App\Service\OrderByService;
use App\State\HtmlProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LinkDeleteProcessor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LinkRepository $linkRepository,
        private OrderByService $orderByService,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): HtmlProcessorResult
    {
        /** @var Link $entity */
        $position = $entity->getPosition();

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $links = $this->linkRepository->findByPosition($position);
        $this->orderByService->resetOrders($links);

    
        return new HtmlProcessorResult(
            success: true,
            messageKey: 'link.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
    }
}
