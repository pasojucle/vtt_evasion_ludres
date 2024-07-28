<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;

enum IdentityKindEnum: string
{
    case MEMBER = 'member';

    case KINSHIP = 'kinship';

    case SECOND_CONTACT = 'second_contact';

    use EnumTrait;
}
