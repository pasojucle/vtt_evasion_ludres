<?php

declare(strict_types=1);

namespace App\Dto\View;

readonly class EmptyView
{
    public function __construct(
        public string $message,
        public string $icon,
    ) {
    }

    public function getName(): string
    {
        return 'empty';
    }

    public function getTemplate(): string
    {
        return 'components/_empty.html.twig';
    }
}
