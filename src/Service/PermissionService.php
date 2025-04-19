<?php

namespace App\Service;

use App\Entity\Enum\PermissionEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class PermissionService
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    )
    {
        
    }

    public function getChoicesFilter(): array
    {
        $permissionChoices = [];
        /** @ver PermissionEnum $permission */
        foreach (PermissionEnum::cases() as $permission) {
            $permissionChoices[] = ['id' => $permission->value, 'name' => $permission->trans($this->translator)];
        }

        return $permissionChoices;
    }
}
