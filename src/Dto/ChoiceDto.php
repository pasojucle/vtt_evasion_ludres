<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\DtoTransformer\AbstractApiTransformer;

class ChoiceDto extends AbstractApiTransformer
{
    public null|int|string $id = null;

    public ?string $name = null;

    public ?string $label = null;

    public ?string $target = null;

    public null|bool|int $value = null;

    public ?int $group = null;
}
