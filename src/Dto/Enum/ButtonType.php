<?php

declare(strict_types=1);

namespace App\Dto\Enum;

enum ButtonType: string
{
    case SUBMIT = 'submit';

    case BUTTON = 'button';
}
