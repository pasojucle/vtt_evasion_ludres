<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\View\EmptyView;
use App\Dto\View\LinkView;

readonly class ListDrawerView
{
    /**
     * @param ListDrawerItemView[] $items
     */
    public function __construct(
        public string $title,
        public string $description,
        public array $items,
        public EmptyView $empty,
    ) {
    }

    public function getName(): string
    {
        return 'sheet';
    }

    public function getTemplate(): string
    {
        return 'components/list/_drawer.sheet.html.twig';
    }

    public function getFormAttr(): array
    {
        return [
            'data-controller' => 'filter',
            'data-turbo-frame' => LinkView::SHEET_CONTENT,
        ];
    }
}
