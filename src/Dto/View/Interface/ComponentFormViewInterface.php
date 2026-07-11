<?php

declare(strict_types=1);

namespace App\Dto\View\Interface;

interface ComponentFormViewInterface
{
    public function getTemplate(): string;

    public function getFormAttr(): array;
}
