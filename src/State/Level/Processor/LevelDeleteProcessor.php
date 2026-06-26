<?php

declare(strict_types=1);

namespace App\State\Level\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Level;
use App\Repository\LevelRepository;
use App\Service\OrderByService;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LevelDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LevelRepository $levelRepository,
        private OrderByService $orderByService,
    ) {
    }

    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        /** @var Level $entity */
        $type = $entity->getType();

        $entity->setIsDeleted(true);
        $this->entityManager->flush();

        $levels = $this->levelRepository->findByType($type);
        $this->orderByService->resetOrders($levels);

        return new ProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'level.flash.success.delete',
        );
    }
}
