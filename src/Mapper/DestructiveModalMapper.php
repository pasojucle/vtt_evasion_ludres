<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\DialogModalDto;
use App\Dto\Enum\DialogType;

class DestructiveModalMapper
{
    public function mapToView(string $confirmationMessage): DialogModalDto
    {
        return new DialogModalDto(
            type: DialogType::DESTRUCTIVE,
            title: 'Suppression',
            action: 'Supprimer',
            message: $confirmationMessage,
            icon: 'lucide:delete'
        );
    }
}