<?php

declare(strict_types=1);

namespace App\State\Level\Processor;

use App\Dto\State\JsonProcessorResult;
use App\Dto\Payload\LevelOrderDto;
use App\Repository\LevelRepository;
use App\Service\OrderByService;
use App\State\Interface\JsonProcessorInterface;

class LevelOrderProcessor implements JsonProcessorInterface
{
    public function __construct(
        private LevelRepository $levelRepository,
        private OrderByService $orderByService,
    ) {
    }

    /**
     * @param LevelOrderDto $object
     */
    public function process(object $object): JsonProcessorResult
    {
        $level = $object->level;
        $type = $level->getType();
        $levels = $this->levelRepository->findByType($type);

        $this->orderByService->setNewOrders($level, $levels, $object->newOrder);

        return new JsonProcessorResult(
            success: true,
            data: [],
        );
    }
}
