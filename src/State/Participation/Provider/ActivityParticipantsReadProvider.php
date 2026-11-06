<?php

declare(strict_types=1);

namespace App\State\Participation\Provider;

use App\Core\Contract\Provider\ListDrawerProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\ActivityParticipantsFilter;
use App\Dto\View\ListDrawerView;
use App\Entity\BikeRide;
use App\Mapper\Participation\ActivityParticipantsReadMapper;
use App\Repository\SessionRepository;
use App\Service\UrlContextService;
use Doctrine\ORM\QueryBuilder;

/**
 * @implements ListDrawerProviderInterface<ActivityParticipantsFilter>
 */
class ActivityParticipantsReadProvider implements ListDrawerProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private SessionRepository $sessionRepository,
        private ActivityParticipantsReadMapper $activityParticipantsReadMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function getCollection(AbstractFilter $filter, HandlerContext $context): ListDrawerView
    {
        return $this->activityParticipantsReadMapper->mapToView(
            $this->getQueryBuilder($filter->activity)->getQuery()->getResult(),
            $filter,
            $this->urlContextService->decodeUrl($context->encodedFallback)
        );
    }

    private function getQueryBuilder(BikeRide $activity): QueryBuilder
    {
        $qb = $this->sessionRepository->getSessionQuery();

        $this->sessionRepository->filterParticipated($qb);

        $this->sessionRepository->filterActivity($qb, $activity);

        $this->sessionRepository->filterSortByUser($qb);

        return $qb;
    }
}
