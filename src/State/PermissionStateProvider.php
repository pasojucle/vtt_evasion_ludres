<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\DtoTransformer\ChoiceDtoTransformer;
use App\Entity\Enum\PermissionEnum;
use Doctrine\Common\Collections\ArrayCollection;

class PermissionStateProvider implements ProviderInterface
{
    public function __construct(
        private ChoiceDtoTransformer $transformer,
    ) {
    }
    
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $permissionChoices = [];
        /** @var PermissionEnum $permission */
        foreach (PermissionEnum::cases() as $permission) {
            $permissionDto = $this->transformer->fromEnum($permission);
            $permissionChoices[] = $permissionDto->toArray();
        }

        return new ArrayCollection($permissionChoices);
    }
}
