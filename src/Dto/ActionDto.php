<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\DtoTransformer\AbstractApiTransformer;

class ActionDto extends AbstractApiTransformer
{
    public ?string $url = null;

    public string $label;

    public ?string $icon = null;

    public bool $openInModal = false;
}
