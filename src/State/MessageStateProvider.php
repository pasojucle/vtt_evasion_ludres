<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\DtoTransformer\ActionDtoTransformer;
use Doctrine\Common\Collections\ArrayCollection;

class MessageStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly CollectionProvider $collectionProvider,
        private readonly ActionDtoTransformer $transformer,
    ) {
    }
    
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $messages = [];
            foreach ($this->collectionProvider->provide($operation, $uriVariables, $context) as $message) {
                $messages[] = $this->transformer->fromMessage($message)->toArray();
            }

            return new ArrayCollection($messages);
        }

        return null;
    }
}
