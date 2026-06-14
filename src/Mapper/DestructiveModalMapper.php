<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\View\DialogModalView;
use App\Dto\Enum\DialogType;

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
