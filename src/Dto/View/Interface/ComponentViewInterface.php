<?php

declare(strict_types=1);

namespace App\Dto\View\Interface;

interface ComponentViewInterface
{
    public function getName(): string;
    public function getTemplate(): string;
}
