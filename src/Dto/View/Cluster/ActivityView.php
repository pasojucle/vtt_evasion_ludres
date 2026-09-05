<?php

declare(strict_types=1);

namespace App\Dto\View\Cluster;

use App\Dto\View\Interface\TabWrapperHeaderInterface;

readonly class ActivityView implements TabWrapperHeaderInterface
{
    public function __construct(
        public int $id,
        public string $title,
        public string $period,
    ){}

    public function getName(): string
    {
        return 'header';
    }

    public function getTemplate():string
    {
        return 'cluster/admin/activity/tab_header.html.twig';
    }
}