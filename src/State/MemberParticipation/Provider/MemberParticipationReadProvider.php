<?php

declare(strict_types=1);

namespace App\State\MemberParticipation\Provider;

use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\MemberParticipationFilter;
use App\Dto\View\MemberParticipation\MemberActivitiesView;
use App\Entity\Member;
use App\Mapper\MemberParticipation\MemberParticipationReadMapper;
use App\Repository\IndemnityRepository;
use App\Repository\SessionRepository;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;
use Doctrine\ORM\QueryBuilder;


class MemberParticipationReadProvider
{
    use FilterHydratorTrait;

    public function __construct(
        private IndemnityRepository $indemnityRepository,
        private MemberParticipationReadMapper $memberParticipationReadMapper,
        private SessionRepository $sessionRepository,
        private PaginatorService $paginator,
    ) {
    }

    /**
     * Summary of getCollection
     * @param MemberParticipationFilter $filter
     */
    public function getCollection(
        object $entity, 
        AbstractFilter $filter,
        string $route, 
        ?int $currentPage = 1
    ): MemberActivitiesView
    {
        $qb = $this->getQueryBuilder( $entity, $filter);
        $sessions = $this->paginator->paginate(
            $qb,
            $currentPage,
            PaginatorService::PAGINATOR_PER_PAGE
        );

        return $this->memberParticipationReadMapper->mapToView(
            $entity, 
            $this->indemnityRepository->findAll(),
            $sessions,
            $route,
            $currentPage,
            $filter,
        );
        
    }

    public function updateLazyTemplate(): string
    {

        return 'member_participation/update.lazy.html.twig';
    }

    private function getQueryBuilder(Member $member, MemberParticipationFilter $filter): QueryBuilder
    {
        $qb = $this->sessionRepository->getSessionQuery();

        $this->sessionRepository->filterUser($qb, $member);

        $this->sessionRepository->filterparticipated($qb);

        if ($filter->startAt && $filter->endAt) {
            $this->sessionRepository->filterPeriod($qb, $filter->startAt, $filter->endAt);
        };
        
        if ($filter->sort) {
            $this->sessionRepository->filterSort($qb, $filter->sort);
        }

        if ($filter->type) {
            $this->sessionRepository->filterType($qb, $filter->type);
        }

        return $qb;
    }
}
