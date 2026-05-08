<?php

declare(strict_types=1);

namespace App\Dto\Enum;

enum Size: string
{
    case ICON = 'icon';

    case SM = 'sm';

    case MD = 'md';

    case LG = 'lg';
}
