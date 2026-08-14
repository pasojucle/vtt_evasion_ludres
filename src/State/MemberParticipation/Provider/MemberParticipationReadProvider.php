<?php

declare(strict_types=1);

namespace App\State\MemberParticipation\Provider;

use App\Dto\Filter\MemberParticipationFilter;
use App\Dto\View\MemberParticipation\MemberActivitiesView;
use App\Dto\View\SheetView;
use App\Entity\Enum\LevelType;
use App\Mapper\MemberParticipation\MemberParticipationReadMapper;
use App\Repository\IndemnityRepository;
use App\Repository\SessionRepository;
use App\Service\Indemnity\ComputeParticipationIndemnity;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;
use App\State\Interface\TurboStreamProviderInterface;
use App\State\MemberParticipation\Enum\QueryScope;
use Doctrine\ORM\QueryBuilder;

/**
 * @implements TurboStreamProviderInterface<MemberParticipationFilter>
 */
class MemberParticipationReadProvider implements TurboStreamProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private IndemnityRepository $indemnityRepository,
        private MemberParticipationReadMapper $memberParticipationReadMapper,
        private SessionRepository $sessionRepository,
        private PaginatorService $paginator,
        private ComputeParticipationIndemnity $computeParticipationIndemnity,
    ) {
    }


    public function getFormView(object $entity, ?string $fallback = null): SheetView
    {
        return new SheetView(
            title: 'Modifier',
            description: 'Modifier le filtre de recherche',
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $entity): array
    {
        return [];
    }

    public function getStreamView(object $entity, array $context = []): MemberActivitiesView
    {
        $currentPage = $context['page'];

        $qb = $this->getQueryBuilder($entity);
        $allPeriodSessions = $this->getQueryBuilder($entity, QueryScope::INDEMNITY)->getQuery()->getResult();

        $indemnityMap = $this->mapIndemnities($this->indemnityRepository->findAll());

        $globalResult = ($this->computeParticipationIndemnity)(
            sessions: $allPeriodSessions,
            level: $entity->member?->getLevel(),
            indemnityMap: $indemnityMap
        );

        return $this->memberParticipationReadMapper->mapToView(
            filter: $entity,
            totalIndemnity: $globalResult->hasIndemnity() ? $globalResult->totalAmount : null,
            sessionAmounts: $globalResult->amountsBySessionId,
            paginatedSessions: $this->paginator->paginate(
                $qb,
                $currentPage,
                PaginatorService::PAGINATOR_PER_PAGE
            ),
            route: $context['route'],
            currentPage: $currentPage,
        );
    }

    private function getQueryBuilder(MemberParticipationFilter $filter, QueryScope $scope = QueryScope::LIST): QueryBuilder
    {
        $qb = $this->sessionRepository->getSessionQuery();

        $this->sessionRepository->filterUser($qb, $filter->member);

        $this->sessionRepository->filterparticipated($qb);

        if ($filter->startAt && $filter->endAt) {
            $this->sessionRepository->filterPeriod($qb, $filter->startAt, $filter->endAt);
        };


        if (QueryScope::LIST === $scope) {
            if ($filter->sort) {
                $this->sessionRepository->filterSort($qb, $filter->sort);
            }

            if ($filter->type) {
                $this->sessionRepository->filterType($qb, $filter->type);
            }
        }


        return $qb;
    }

    private function mapIndemnities(array $indemnities): array
    {
        $indemnityMap = [];
        foreach ($indemnities as $indemnity) {
            $key = sprintf(
                '%d_%d',
                $indemnity->getLevel()->getId(),
                $indemnity->getBikeRideType()->getId()
            );
            $indemnityMap[$key] = $indemnity->getAmount();
        }

        return $indemnityMap;
    }

    private function getAmount(MemberParticipationFilter $filter, array $indemnityMap): ?string
    {
        $member = $filter->member;
        $level = $member->getLevel();

        if (!$level || LevelType::FRAME !== $level->getType()) {
            return null;
        }

        $levelId = $level->getId();
        $qb = $this->getQueryBuilder($filter, QueryScope::INDEMNITY);

        $amount = 0;
        foreach ($qb->getQuery()->getResult() as $session) {
            $bikeRide = $session->getCluster()->getBikeRide();
            $bikeRideTypeId = $bikeRide->getBikeRideType()->getId();
            $amount += $indemnityMap["{$levelId}_{$bikeRideTypeId}"] ?? 0.0;
        }

        return (string) $amount;
    }
}
