<?php

declare(strict_types=1);

namespace App\Dto\View\ClusterSkill;

use App\Core\Contract\View\ComponentViewInterface;
use App\Dto\View\EmptyView;

readonly class ClusterSkillsSheetView implements ComponentViewInterface
{
    /**
     * @param string $title
     * @param string $description
     * @param string $action
     * @param ClusterSkillView[] $items
     */
    public function __construct(
        public string $title,
        public string $description,
        public string $action,
        public array $items,
        public EmptyView $empty,
    ) {
    }

    public function getName(): string
    {
        return 'view';
    }

    public function getTemplate(): string
    {
        return 'cluster_skill/admin/_detail.sheet.html.twig';
    }
}
