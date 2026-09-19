<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Core\Contract\View\ComponentViewInterface;

readonly class ProgressBarView implements ComponentViewInterface
{
    public function __construct(
        public string $title,
        public int $value,
        public int $percentage,
    ) {
    }

    public function getName(): string
    {
        return 'progress_bar';
    }

    public function getTemplate(): string
    {
        return 'components/_progress_bar.html.twig';
    }
}
