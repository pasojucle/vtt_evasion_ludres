<?php

declare(strict_types=1);

namespace App\Dto\View\Interface;

interface LoadMoreViewInterface extends TurboStreamViewInterface
{
    public function getTemplate(): string;

    public function getStreamLoadMoreTemplate(): string;
}
