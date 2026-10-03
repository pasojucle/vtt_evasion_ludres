<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\Enum\DialogType;
use App\Dto\View\DialogModalView;

class DialogueModalMapper
{
    public function mapToView(
        DialogType $type,
        string $title,
        string $action,
        string $confirmationMessage,
        string $icon,
    ): DialogModalView {
        return new DialogModalView(
            type: $type,
            title: $title,
            action: $action,
            message: $confirmationMessage,
            icon: $icon
        );
    }
}
