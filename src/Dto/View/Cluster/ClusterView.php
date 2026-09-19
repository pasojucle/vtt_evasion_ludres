<?php

declare(strict_types=1);

namespace App\Dto\View\Cluster;

use App\Core\Contract\View\TabContentInterface;
use App\Core\Contract\View\TurboStreamViewInterface;
use App\Dto\View\BadgeView;
use App\Dto\View\DropdownView;
use App\Dto\View\LinkView;
use App\Dto\View\WidgetView;

readonly class ClusterView implements TabContentInterface, TurboStreamViewInterface
{
    /**
     * @param WidgetView[] $widgets
     * @param LinkView[] $actions
     */
    public function __construct(
        public int $id,
        public string $cardTitle,
        public BadgeView $pratice,
        public array $widgets,
        public bool $isComplete,
        public array $participants,
        public bool $isEditable,
        public array $actions,
        public DropdownView $dropdown,
    ) {
    }

    public function getName(): string
    {
        return 'view';
    }

    public function getTemplate(): string
    {
        return 'cluster/admin/show/index.html.twig';
    }

    public function getStreamTemplate(): string
    {
        return 'cluster/admin/show/update.lazy.html.twig';
    }
}
