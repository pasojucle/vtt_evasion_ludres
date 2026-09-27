<?php

declare(strict_types=1);

namespace App\State\Activity\Provider;

use App\Core\Contract\Provider\ListDrawerProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\ActivityFramersFilter;
use App\Dto\View\ListDrawerView;
use App\Entity\BikeRide;
use App\Entity\Enum\AvailabilityEnum;
use App\Mapper\Activity\Framers\ActivityFramersReadMapper;
use App\Repository\MemberRepository;
use App\Service\UrlContextService;
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
        private UrlContextService $urlContextService,
    ) {
    }

    public function getCollection(AbstractFilter $filter, HandlerContext $context): ListDrawerView
    {
        $activity = $context->parent;
        $qb = $this->getQueryBuilder($activity, $filter);
        
        return $this->activityFramersMapper->mapToView(
            $qb->getQuery()->getResult(),
            $this->urlContextService->decodeUrl($context->encodedFallback),
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
