<?php

declare(strict_types=1);

namespace App\Dto\View\Cluster;

use App\Dto\View\BadgeView;
use App\Dto\View\WidgetView;
use App\Dto\View\DropdownView;
use App\Dto\View\Interface\TabContentInterface;
use App\Dto\View\LinkView;

readonly class ClusterView implements TabContentInterface
{
    /**
     * @param WidgetView[] $widgets
     * @param LinkView[] $actions
     */
    public function __construct(
        public int $id,
        public string $title,
        public BadgeView $pratice,
        public array $widgets,
        public bool $isComplete,
        public array $participants,
        public bool $hasSkills,
        public bool $isEditable,
        public array $actions,
        public DropdownView $dropdown,
    ){}

    public function getName(): string
    {
        return 'view';
    }

    public function getTemplate(): string
    {
        return 'cluster/admin/show/index.html.twig';
    }
}