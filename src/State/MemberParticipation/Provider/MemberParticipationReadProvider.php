<?php

declare(strict_types=1);

namespace App\State\MemberParticipation\Provider;

use App\Core\Contract\Provider\ListLoadMoreProviderInterface;
use App\Core\Contract\Provider\StreamListExportableInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\MemberParticipationFilter;
use App\Dto\View\MemberParticipation\MemberActivitiesView;
use App\Dto\View\SheetView;
use App\Entity\Member;
use App\Mapper\MemberParticipation\MemberParticipationExportMapper;
use App\Mapper\MemberParticipation\MemberParticipationReadMapper;
use App\Repository\IndemnityRepository;
use App\Repository\SessionRepository;
use App\Service\Indemnity\ComputeParticipationIndemnity;
use App\Service\PaginatorService;
use App\State\MemberParticipation\Enum\QueryScope;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\QueryBuilder;

class MemberParticipationReadProvider implements ListLoadMoreProviderInterface, StreamListExportableInterface
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


    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        return new SheetView(
            title: 'Modifier',
            description: 'Modifier le filtre de recherche',
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'attr' => [
                'data-controller' => 'form-modifier',
            ],
        ];
    }

    /**
     * @param MemberParticipationFilter $data
     */
    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): MemberActivitiesView
    {
        $currentPage = $context->page;
        $member = $context->parent;
        $filterConfig = $this->getFilterConfig('admin_member_participation_filter');

        $qb = $this->getQueryBuilder($data, $member);
        $allPeriodSessions = $this->getQueryBuilder($data, $member, QueryScope::INDEMNITY)->getQuery()->getResult();

        $indemnityMap = $this->mapIndemnities($this->indemnityRepository->findAll());

        $computeParticipationIndemnity = new ComputeParticipationIndemnity();
        $globalResult = $computeParticipationIndemnity(
            sessions: $allPeriodSessions,
            level: $member?->getLevel(),
            indemnityMap: $indemnityMap
        );

        return $this->memberParticipationReadMapper->mapToView(
            member: $member,
            filter: $data,
            filterConfig: $filterConfig,
            totalIndemnity: $globalResult->hasIndemnity() ? $globalResult->totalAmount : null,
            sessionAmounts: $globalResult->amountsBySessionId,
            paginatedSessions: $this->paginator->paginate(
                $qb,
                $currentPage,
                PaginatorService::PAGINATOR_PER_PAGE
            ),
            lineChartParticipations: array_column($this->getParticipations($data, $member), 'total', 'month'),
            lineChartPeriod: $this->getPeriod($data->startAt, $data->endAt),
            context: $context,
        );
    }
        
    /**
     * @param MemberParticipationFilter $filter
     */
    public function streamExportContent(AbstractFilter $filter, ?HandlerContext $context = null): void
    {
        $entities = $this->getQueryBuilder($filter, $context->parent)->getQuery()->getResult();

        $this->memberParticipationExportMapper->streamToCsv($entities, $context->parent, $filter);
    }

    private function getQueryBuilder(MemberParticipationFilter $filter, Member $member, QueryScope $scope = QueryScope::LIST): QueryBuilder
    {
        $qb = $this->sessionRepository->getSessionQuery();

        $this->sessionRepository->filterUser($qb, $member);

        $this->sessionRepository->filterParticipated($qb);

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

    private function getParticipations(MemberParticipationFilter $filter, Member $member): array
    {
        $qb = $this->sessionRepository->getTotalSessionByMonthQuery();

        $this->sessionRepository->filterUser($qb, $member);

        $this->sessionRepository->filterParticipated($qb);

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

    public function filename(?HandlerContext $context = null): string
    {
        return sprintf('export_participation_%s.csv', $context->parent->getLicenceNumber());
    }
}
