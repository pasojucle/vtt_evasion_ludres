<?php

declare(strict_types=1);

namespace App\State\MemberParticipation\Provider;

use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\MemberParticipationFilter;
use App\Dto\State\TurboStreamContext;
use App\Dto\View\MemberParticipation\MemberActivitiesView;
use App\Dto\View\SheetView;
use App\Mapper\MemberParticipation\MemberParticipationExportMapper;
use App\Mapper\MemberParticipation\MemberParticipationReadMapper;
use App\Repository\IndemnityRepository;
use App\Repository\SessionRepository;
use App\Service\Indemnity\ComputeParticipationIndemnity;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;
use App\State\Interface\ListLoadMoreProviderInterface;
use App\State\Interface\StreamExportableInterface;
use App\State\MemberParticipation\Enum\QueryScope;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\QueryBuilder;

class MemberParticipationReadProvider implements ListLoadMoreProviderInterface, StreamExportableInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private IndemnityRepository $indemnityRepository,
        private MemberParticipationReadMapper $memberParticipationReadMapper,
        private MemberParticipationExportMapper $memberParticipationExportMapper,
        private SessionRepository $sessionRepository,
        private PaginatorService $paginator,
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

    /**
     * @param MemberParticipationFilter $entity
     */
    public function getStreamView(object $entity, ?TurboStreamContext $context = null): MemberActivitiesView
    {
        $currentPage = $context->page;
        $member = $context->object;
        $entity->member = $member;
        $filterConfig = $this->getFilterConfig('admin_member_participation_filter');


        $qb = $this->getQueryBuilder($entity);
        $allPeriodSessions = $this->getQueryBuilder($entity, QueryScope::INDEMNITY)->getQuery()->getResult();

        $indemnityMap = $this->mapIndemnities($this->indemnityRepository->findAll());

        $computeParticipationIndemnity = new ComputeParticipationIndemnity();
        $globalResult = $computeParticipationIndemnity(
            sessions: $allPeriodSessions,
            level: $entity->member?->getLevel(),
            indemnityMap: $indemnityMap
        );

        return $this->memberParticipationReadMapper->mapToView(
            filter: $entity,
            filterConfig: $filterConfig,
            totalIndemnity: $globalResult->hasIndemnity() ? $globalResult->totalAmount : null,
            sessionAmounts: $globalResult->amountsBySessionId,
            paginatedSessions: $this->paginator->paginate(
                $qb,
                $currentPage,
                PaginatorService::PAGINATOR_PER_PAGE
            ),
            lineChartParticipations: array_column($this->getParticipations($entity), 'total', 'month'),
            lineChartPeriod: $this->getPeriod($entity->startAt, $entity->endAt),
            route: $context->route,
            currentPage: $currentPage,
        );
    }
        
    /**
     * @param MemberParticipationFilter $filter
     */
    public function streamExportContent(AbstractFilter $filter): void
    {
        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        $this->memberParticipationExportMapper->streamToCsv($entities, $filter);
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

    private function getParticipations(MemberParticipationFilter $filter): array
    {
        $qb = $this->sessionRepository->getCountSessionQuery();

        $this->sessionRepository->filterUser($qb, $filter->member);

        $this->sessionRepository->filterparticipated($qb);

        if ($filter->startAt && $filter->endAt) {
            $this->sessionRepository->filterPeriod($qb, $filter->startAt, $filter->endAt);
        };


        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @return DateTimeImmutable[]
     */
    public function getPeriod(DateTimeInterface $startAt, DateTimeInterface $endAt): array
    {
        $current = DateTimeImmutable::createFromInterface($startAt)->modify('first day of this month');
        $end = DateTimeImmutable::createFromInterface($endAt)->modify('first day of this month');

        $months = [];

        while ($current <= $end) {
            $months[] = $current;
            $current = $current->modify('+1 month');
        }

        return $months;
    }
}
