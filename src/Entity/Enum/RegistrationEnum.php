<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;

enum RegistrationEnum: string
{
    case NONE = 'none';
    case SCHOOL = 'school';
    case CLUSTERS = 'cluster';

    use EnumTrait;
}
