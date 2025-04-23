<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\DtoTransformer\ChoiceDtoTransformer;
use App\Entity\Level;
use App\Service\LevelService;
use Doctrine\Common\Collections\ArrayCollection;

class LevelChoicesStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly CollectionProvider $collectionProvider,
        private readonly ChoiceDtoTransformer $transformer,
    ) {
    }
    
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $memberLevels = [
                $this->transformer->fromLevel(null, ['label' => Level::TYPES[Level::TYPE_SCHOOL_MEMBER]])->toArray(),
                $this->transformer->fromLevel(null, ['id' => sprintf('group-%s', Level::TYPE_SCHOOL_MEMBER), 'title' => LevelService::LEVEL_ALL_MEMBER, 'target' => 'level.type', 'value' => Level::TYPE_SCHOOL_MEMBER, 'group' => Level::TYPE_ADULT_MEMBER])->toArray(),
            ];
            $frameLevels = [
                $this->transformer->fromLevel(null, ['label' => Level::TYPES[Level::TYPE_FRAME]])->toArray(),
                $this->transformer->fromLevel(null, ['id' => sprintf('group-%s', Level::TYPE_FRAME), 'title' => LevelService::LEVEL_ALL_FRAME, 'target' => 'level.type', 'value' => Level::TYPE_FRAME, 'group' => Level::TYPE_FRAME])->toArray(),
            ];
            foreach ($this->collectionProvider->provide($operation, $uriVariables, $context) as $level) {
                match ($level->getType()) {
                    Level::TYPE_SCHOOL_MEMBER => $memberLevels[] = $this->transformer->fromLevel($level, ['group' => Level::TYPE_ADULT_MEMBER])->toArray(),
                    Level::TYPE_FRAME => $frameLevels[] = $this->transformer->fromLevel($level, ['group' => Level::TYPE_FRAME])->toArray(),
                    default => $levelChoices[] = $this->transformer->fromLevel($level)->toArray(),
                };
            }
    
            $levelChoices[] = $this->transformer->fromLevel(null, ['id' => sprintf('board-member-%s', Level::TYPE_BOARD_MEMBER), 'title' => 'Membres du bureau et comité', 'target' => 'isBoardMember', 'value' => true])->toArray();
    
            $levelChoices = array_merge($memberLevels, $frameLevels, $levelChoices);
    
            return new ArrayCollection($levelChoices);
        }

        return null;
    }
}
