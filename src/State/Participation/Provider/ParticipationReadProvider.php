<?php

declare(strict_types=1);

namespace App\State\Participation\Provider;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Contract\Provider\ListProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\ParticipationFilter;
use App\Dto\View\ListView;
use App\Mapper\LevelFilterMapper;
use App\Mapper\Participation\ParticipationListMapper;
use App\Repository\ParticipationRepository;
use App\Repository\SessionRepository;
use App\Service\PaginatorService;
use Doctrine\ORM\QueryBuilder;

/**
 * @implements ListProviderInterface<ParticipationFilter>
 */
class ParticipationReadProvider implements ListProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private SessionRepository $sessionRepository,
        private PaginatorService $paginator,
        private ParticipationListMapper $mapper,
        private LevelFilterMapper $levelFilterMapper,
    ) {
    }

    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, HandlerContext $context): ListView
    {
        $qb = $this->getQueryBuilder($filter);

        $entities = $this->paginator->paginate(
            $qb,
            $context->page,
            $filter->itemsPerPage ?? PaginatorService::PAGINATOR_PER_PAGE
        );

        return $this->mapper->mapToView(
            $entities,
            $context,
            $filter,
            $filterConfig
        );
    }

    private function getQueryBuilder(ParticipationFilter $filter): QueryBuilder
    {
        $qb = $this->sessionRepository->getSessionQuery();

        $this->sessionRepository->filterParticipated($qb);

        if ($filter->startAt && $filter->endAt) {
            $this->sessionRepository->filterPeriod($qb, $filter->startAt, $filter->endAt);
        };

        if (!empty($filter->levels)) {
            [$levelTypes, $levels] = $this->levelFilterMapper->parseRawLevels($filter->levels);
            $this->sessionRepository->filterLevels($qb, $levelTypes, $levels);
        }

        if ($filter->practice) {
            $this->sessionRepository->filterPractice($qb, $filter->practice);
        }

        return $qb;
    }
}
