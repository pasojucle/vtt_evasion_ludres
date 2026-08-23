<?php

declare(strict_types=1);

namespace App\Dto\View\MemberSkill;

use App\Dto\View\FilterChipView;
use App\Dto\View\Interface\LoadMoreViewInterface;
use App\Dto\View\LinkView;
use App\Dto\View\MemberSkill\MemberSkillView;

readonly class MemberSkillsView implements LoadMoreViewInterface
{
    /**
     * @param MemberSkillView[] $skills
     * @param FilterChipView[] $filterChips
     */
    public function __construct(
        public int $memberId,
        public array $queries,
        public ?string $category,
        public ?string $level,
        public array $filterChips,
        public LinkView $filterAction,
        public LinkView $addAction,
        public ?LinkView $loadMoreAction,
        public int $counter,
        public array $skills,
    ) {
    }

    public function getTemplate(): string
    {
        return 'member_skill/admin/show.html.twig';
    }

    public function getStreamLoadMoreTemplate(): string
    {
        return 'member_skill/admin/load_more.lazy.html.twig';
    }

    public function getStreamTemplate(): string
    {
        return 'member_skill/admin/filter.lazy.html.twig';
    }
}
