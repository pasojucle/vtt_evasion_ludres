<?php

declare(strict_types=1);

namespace App\State\Coverage\Provider;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Contract\Provider\ListProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\CoverageFilter;
use App\Dto\View\ListView;
use App\Mapper\Coverage\CoverageListMapper;
use App\Mapper\LevelFilterMapper;
use App\Repository\MemberRepository;
use App\Service\PaginatorService;
use App\Service\SeasonService;
use App\State\Coverage\Trait\CoverageDataProviderTrait;

class CoverageListProvider implements ListProviderInterface
{
    use FilterHydratorTrait;
    use CoverageDataProviderTrait;

    public function __construct(
        private MemberRepository $memberRepository,
        private PaginatorService $paginator,
        private CoverageListMapper $mapper,
        private LevelFilterMapper $levelFilterMapper,
        private SeasonService $seasonService,
    ) {
    }

    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, HandlerContext $context): ListView
    {
        /**  @var CoverageFilter $filter*/
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
}
