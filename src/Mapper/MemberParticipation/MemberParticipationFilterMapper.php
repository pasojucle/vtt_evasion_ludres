<?php

declare(strict_types=1);

namespace App\Mapper\MemberParticipation;

use App\Dto\Enum\Size;
use App\Dto\Filter\MemberParticipationFilter;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LinkView;
use App\Dto\View\MemberParticipation\MemberParticipationFilterView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MemberParticipationFilterMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function mapToView(
        MemberParticipationFilter $filter,
    ): MemberParticipationFilterView {
        return new MemberParticipationFilterView(
            memberId: $filter->member->getId(),
            queries: $filter->toArray(),
            period: sprintf('Du %s au %s', $filter->startAt->format('d/m/Y'), $filter->endAt->format('d/m/Y')),
            type: $filter->type?->getName(),
            action: new LinkView(
                url: $this->urlGenerator->generate('admin_member_participation_filter_edit', $filter->toArray()),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                ],
            ),
        );
    }
}
