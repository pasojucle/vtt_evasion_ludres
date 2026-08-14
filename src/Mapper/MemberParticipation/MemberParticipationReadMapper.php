<?php

declare(strict_types=1);

namespace App\Mapper\MemberParticipation;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\MemberParticipationFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LinkView;
use App\Dto\View\MemberParticipation\MemberActivitiesView;
use App\Dto\View\MemberParticipation\MemberActivityView;
use App\Entity\Session;
use App\Mapper\BikeRide\BikeRidePeriodMapper;
use App\Model\Currency;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MemberParticipationReadMapper
{
    public function __construct(
        private TranslatorInterface $translator,
        private BikeRidePeriodMapper $bikeRidePeriodMapper,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function mapToView(
        MemberParticipationFilter $filter,
        ?float $totalIndemnity,
        array $sessionAmounts,
        Paginator $paginatedSessions,
        string $route,
        int $currentPage,
    ): MemberActivitiesView {
        $member = $filter->member;

        return new MemberActivitiesView(
            memberId: $member->getId(),
            queries: $filter->toArray(),
            period: sprintf('Du %s au %s', $filter->startAt->format('d/m/Y'), $filter->endAt->format('d/m/Y')),
            type: $filter->type?->getName(),
            action: new LinkView(
                url: $this->urlGenerator->generate('admin_member_participation_filter', $filter->toArray()),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                ],
            ),
            counter: $paginatedSessions->count(),
            activities: array_map(function (Session $session) use ($sessionAmounts) {
                $bikeRide = $session->getCluster()->getBikeRide();
                $amount = $sessionAmounts[$session->getId()] ?? null;

                return new MemberActivityView(
                    period: $this->bikeRidePeriodMapper->mapToView($bikeRide),
                    title: $bikeRide->getTitle(),
                    practice: $session->isPresent()
                        ? new BadgeView(
                            $session->getPractice()->trans($this->translator),
                            ColorVariant::SUCCESS,
                        )
                        : new BadgeView(
                            'Absent',
                            ColorVariant::DESTRUCTIVE,
                        ),
                    indemnity: $amount
                        ? new BadgeView((new Currency($amount))->toString())
                        : null,
                );
            }, iterator_to_array($paginatedSessions)),
            totalIndemnity: ($totalIndemnity)
                ? new BadgeView((new Currency($totalIndemnity))->toString())
                : null,
        );
    }
}
