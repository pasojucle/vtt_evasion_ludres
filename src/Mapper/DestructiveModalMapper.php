<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\Enum\DialogType;
use App\Dto\View\DialogModalView;

class DestructiveModalMapper
{
    public function mapToView(string $confirmationMessage): DialogModalView
    {
        return new DialogModalView(
            type: DialogType::DESTRUCTIVE,
            title: 'Suppression',
            action: 'Supprimer',
            message: $confirmationMessage,
            icon: 'lucide:delete'
        );
    }
}
