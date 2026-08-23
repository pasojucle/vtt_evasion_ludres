<?php

declare(strict_types=1);

namespace App\Dto\View\MemberParticipation;

use App\Dto\View\BadgeView;
use App\Dto\View\FilterChipView;
use App\Dto\View\Interface\LoadMoreViewInterface;
use App\Dto\View\LinkView;

readonly class MemberActivitiesView implements LoadMoreViewInterface
{
    /**
     * @param MemberActivityView[] $activities
     * @param FilterChipView[] $filterChips
     */
    public function __construct(
        public int $memberId,
        public array $queries,
        public string $period,
        public ?string $type,
        public array $filterChips,
        public LinkView $filterAction,
        public LinkView $exportAction,
        public ?LinkView $loadMoreAction,
        public int $counter,
        public array $activities,
        public ?BadgeView $totalIndemnity,
        public string $lineChartParticipations,
    ) {
    }

    public function getTemplate(): string
    {
        return 'member_participation/admin/show.html.twig';
    }

    public function getStreamLoadMoreTemplate(): string
    {
        return 'member_participation/admin/load_more.lazy.html.twig';
    }

    public function getStreamTemplate(): string
    {
        return 'member_participation/admin/update.lazy.html.twig';
    }
}
