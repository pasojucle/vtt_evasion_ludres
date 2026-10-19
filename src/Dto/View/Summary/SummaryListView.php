<?php

declare(strict_types=1);

namespace App\Dto\View\Summary;

use App\Core\Contract\View\ComponentViewInterface;
use App\Dto\View\ActionCardView;
use App\Dto\View\EmptyView;
use App\Dto\View\LinkView;

readonly class SummaryListView implements ComponentViewInterface
{
    /**
     * @param string $title
     * @param string $description
     * @param LinkView $fallback
     * @param SummaryItemView[] $items
     */
    public function __construct(
        public string $title,
        public string $description,
        public LinkView $fallback,
        public array $items,
        public EmptyView $empty,
        public ActionCardView $addAction,
    ) {
    }

    public function getName(): string
    {
        return 'list';
    }

    public function getTemplate(): string
    {
        return 'summary/admin/list.html.twig';
    }
}
