<?php

declare(strict_types=1);

namespace App\Core\Contract\View;

interface LoadMoreViewInterface extends TurboStreamViewInterface
{
    public function getTemplate(): string;

    public function getStreamLoadMoreTemplate(): string;
}
