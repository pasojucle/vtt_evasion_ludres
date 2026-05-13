<?php

declare(strict_types=1);

namespace App\Dto\Enum;

use App\Entity\Enum\EnumTrait;

enum DropdownVariant: string
{
    case LIST_ITEM = 'list-item';
    
    case BUTTON = 'button';
    
    case GOST = 'gost';

    use EnumTrait;
}
