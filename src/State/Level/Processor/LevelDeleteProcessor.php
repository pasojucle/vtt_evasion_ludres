<?php

declare(strict_types=1);

namespace App\State\Level\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\Level;
use App\Repository\LevelRepository;
use App\Service\FilterDecoderService;
use App\Service\OrderByService;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LevelDeleteProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LevelRepository $levelRepository,
        private OrderByService $orderByService,
        private FilterDecoderService $filterDecoder,
    ) {
    }

    public function process(object $entity, ?string $filter): ProcessorResult
    {
        /** @var Level $entity */
        $type = $entity->getType();

        $entity->setIsDeleted(true);
        $this->entityManager->flush();

        $levels = $this->levelRepository->findByType($type);
        $this->orderByService->ResetOrders($levels);

        return new ProcessorResult(
            success: true,
            targetRoute: 'admin_bike_rides',
            routeParams: $this->filterDecoder->decode($filter),
            messageKey: 'level.flash.success.delete',
        );
    }
}
