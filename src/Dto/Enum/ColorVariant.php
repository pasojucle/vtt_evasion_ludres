<?php

declare(strict_types=1);

namespace App\Dto\Enum;

enum ColorVariant: string
{
    case DEFAULT = 'default';

    case SUCCESS = 'success';

    case DESTRUCTIVE = 'destructive';

    case WARNING = 'warning';

    case ACCENT = 'accent';

    case SKI = 'sky';

    case PINK = 'pink';

    case AMBER = 'amber';

    case INDIGO = 'indogo';

    case YELLOW = 'yellow';

    case DROPDOWN = 'dropdown';
}