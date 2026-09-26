<?php

declare(strict_types=1);

namespace App\State\Activity\Provider;

use App\Core\Contract\Provider\ListDrawerProviderInterface;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\ActivityFramersFilter;
use App\Dto\State\ViewContext;
use App\Dto\View\ListDrawerView;
use App\Entity\BikeRide;
use App\Entity\Enum\AvailabilityEnum;
use App\Mapper\Activity\Framers\ActivityFramersReadMapper;
use App\Repository\MemberRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * @implements ListDrawerProviderInterface<ActivityFramersFilter>
 */
class ActivityFramersReadProvider implements ListDrawerProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private ActivityFramersReadMapper $activityFramersMapper,
        private MemberRepository $memberRepository,
    ) {
    }

    public function getCollection(AbstractFilter $filter, ViewContext $context): ListDrawerView
    {
        $activity = $context->parent;
        $qb = $this->getQueryBuilder($activity, $filter);
        
        return $this->activityFramersMapper->mapToView(
            $qb->getQuery()->getResult(),
            $context->fallback,
        );
    }


    private function getQueryBuilder(BikeRide $bikeRide, ActivityFramersFilter $filter): QueryBuilder
    {
        $qb = $this->memberRepository->getMemberQuery();

        $this->memberRepository->filterLevelType($qb, $filter->levelType);

        if ($filter->member) {
            $this->memberRepository->filterMember($qb, $filter->member->getId());
        }

        match ($filter->availability) {
            null => $this->memberRepository->filterActivity($qb, $bikeRide),
            AvailabilityEnum::UNAVAILABLE => $this->memberRepository->filterActivityAndUnavailability($qb, $bikeRide),
            default => $this->memberRepository->filterActivityAndAvailability($qb, $bikeRide, $filter->availability)
        };

        return $qb;
    }
}
