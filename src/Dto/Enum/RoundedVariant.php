<?php

declare(strict_types=1);

namespace App\Dto\Enum;

use App\Entity\Enum\EnumTrait;

enum RoundedVariant: string
{
    case ROUNDED = 'rounded';

    case ROUNDED_START = 'rounded-start';

    case ROUNDED_END = 'rounded-end';

    case ROUNDED_NONE = 'rounded-none';


    use EnumTrait;
}
