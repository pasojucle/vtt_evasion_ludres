<?php

declare(strict_types=1);

namespace App\Core\Contract\View;

interface ComponentViewInterface
{
    public function getName(): string;
    public function getTemplate(): string;
}
