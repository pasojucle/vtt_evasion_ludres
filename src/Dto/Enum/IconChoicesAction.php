<?php

declare(strict_types=1);

namespace App\Dto\Enum;

enum IconChoicesAction: string
{
    case UPDATE = 'update';

    case PREPEND = 'prepend';

    case APPEND = 'append';
}
