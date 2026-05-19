<?php

declare(strict_types=1);

namespace App\Dto\Enum;

enum DialogType: string
{
    case DEFAULT = 'default';

    case SUCCESS = 'success';

    case DESTRUCTIVE = 'destructive';

    case WARNING = 'warning';
}