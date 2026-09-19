<?php

declare(strict_types=1);

namespace App\Core\Contract\View;

interface TurboStreamViewInterface
{
    public function getStreamTemplate(): string;
}
