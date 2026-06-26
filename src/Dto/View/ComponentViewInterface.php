<?php

declare(strict_types=1);

namespace App\Dto\View;

interface ComponentViewInterface
{
    public function getTemplate(): string;

    public function getFormAttr(): array;
}
