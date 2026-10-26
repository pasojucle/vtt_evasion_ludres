<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Core\Contract\View\TurboStreamViewInterface;
use App\Dto\View\ListItemView;

readonly class ListItemUpdateView implements TurboStreamViewInterface
{
    public function __construct(
        public ListItemView $item,
        public ?FlashesView $flashesView = null,
        public ?string $sortableUrl = null,
    ) {
    }

    public function getStreamTemplate(): string
    {
        return 'components/list/update.lazy.html.twig';
    }
}
