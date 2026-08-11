<?php

declare(strict_types=1);

namespace App\Mapper\MemberParticipation;

use App\Dto\Enum\ColorVariant;
use App\Dto\Filter\MemberParticipationFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\MemberParticipation\MemberActivitiesView;
use App\Dto\View\MemberParticipation\MemberActivityView;
use App\Entity\Member;
use App\Entity\Session;
use App\Mapper\BikeRide\BikeRidePeriodMapper;
use App\Model\Currency;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Contracts\Translation\TranslatorInterface;

class MemberParticipationReadMapper
{
    public function __construct(
        private TranslatorInterface $translator,
        private BikeRidePeriodMapper $bikeRidePeriodMapper,
    ){}

    public function mapToView(
        Member $entity, 
        array $indemnities, 
        Paginator $sessions,
        string $route,
        int $currentPage,
        MemberParticipationFilter $fiter,
    ): MemberActivitiesView
    {
        $levelId = $entity->getLevel()?->getId() ?? 0;
        $indemnityMap = $this->mapIndemnities($indemnities);

        return new MemberActivitiesView(
            memberId: $entity->getId(),
            counter: $sessions->count(),
            activities: array_map(function (Session $session) use ($levelId, $indemnityMap) {
                $bikeRide = $session->getCluster()->getBikeRide();
                $bikeRideTypeId = $bikeRide->getBikeRideType()->getId();
                $amount = $session->isPresent() 
                    ? $indemnityMap["{$levelId}_{$bikeRideTypeId}"] ?? 0.0
                    : null;

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
                }, iterator_to_array($sessions)),
        );
    }

    private function mapIndemnities(array $indemnities): array
    {
        $indemnityMap = [];
        foreach ($indemnities as $indemnity) {
            $key = sprintf('%d_%d', 
                $indemnity->getLevel()->getId(), 
                $indemnity->getBikeRideType()->getId()
            );
            $indemnityMap[$key] = $indemnity->getAmount();
        }

        return $indemnityMap;
    }
}