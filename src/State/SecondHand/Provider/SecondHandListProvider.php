<?php

declare(strict_types=1);

namespace App\State\SecondHand\Provider;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Contract\Provider\ListProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\SecondHandFilter;
use App\Dto\View\ListView;
use App\Mapper\SecondHand\SecondHandListMapper;
use App\Repository\SecondHandRepository;
use App\Service\PaginatorService;
use Doctrine\ORM\QueryBuilder;

/**
 * @implements ListProviderInterface<SecondHandFilter>
 */
class SecondHandListProvider implements ListProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private SecondHandRepository $secondHandRepository,
        private PaginatorService $paginator,
        private SecondHandListMapper $mapper,
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

    private function getQueryBuilder(SecondHandFilter $filter): QueryBuilder
    {
        $qb = $this->secondHandRepository->getSecondHandQuery();

        if ($filter->state) {
            $this->secondHandRepository->filterState($qb, $filter->state);
        }

        if ($filter->category) {
            $this->secondHandRepository->filterCategory($qb, $filter->category);
        }

        if ($filter->member) {
            $this->secondHandRepository->filterMember($qb, $filter->member);
        }

        if ($filter->sort) {
            $this->secondHandRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }
}
