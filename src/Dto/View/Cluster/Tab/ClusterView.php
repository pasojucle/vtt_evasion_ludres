<?php

declare(strict_types=1);

namespace App\Dto\View\Cluster\Tab;

use App\Core\Contract\View\TabContentInterface;
use App\Dto\View\BadgeView;

readonly class ClusterView implements TabContentInterface
{
    public function __construct(
        public int $id,
        public string $url,
        public string $title,
        public BadgeView $pratice,
        public int $total,
    ) {
    }

    public function getName(): string
    {
        return 'content';
    }

    public function getTemplate(): string
    {
        return 'cluster/admin/activity/tab_cluster.html.twig';
    }
}
