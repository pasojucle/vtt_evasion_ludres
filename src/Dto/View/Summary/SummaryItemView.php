<?php

declare(strict_types=1);

namespace App\Dto\View\Summary;

use App\Dto\View\DropdownView;
use App\Dto\View\LinkView;

readonly class SummaryItemView
{
    public function __construct(
        public string $createdAt,
        public string $title,
        public string $content,
        public ?DropdownView $dropdown,
    ) {
    }

    public function getName(): string
    {
        return 'item';
    }

    public function getTemplate(): string
    {
        return 'summary/admin/_item.html.twig';
    }
}
