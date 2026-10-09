<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Core\Contract\View\ComponentViewInterface;

readonly class SheetTabsView implements ComponentViewInterface
{
    /**
     * @param TabView[] $tabs
     */
    public function __construct(
        public array $tabs,
    ) {
    }

    public function getName(): string
    {
        return 'tabs';
    }

    public function getTemplate(): string
    {
        return 'components/sheet/_tabs.sheet.html.twig';
    }
}
