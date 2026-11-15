<?php

declare(strict_types=1);

namespace App\State\User\Provider;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Contract\Provider\FilterInitializerInterface;
use App\Core\Contract\Provider\ListProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\UserFilter;
use App\Dto\View\ListView;
use App\Mapper\LevelFilterMapper;
use App\Mapper\User\UserListMapper;
use App\Repository\MemberRepository;
use App\Service\PaginatorService;
use App\Service\SeasonService;
use App\State\User\Trait\UserListDataTrait;

/**
 * @implements ListProviderInterface<UserFilter>
 */
class UserListProvider implements ListProviderInterface, FilterInitializerInterface
{
    use FilterHydratorTrait;
    use UserListDataTrait;

    public function __construct(
        private MemberRepository $memberRepository,
        private PaginatorService $paginator,
        private LevelFilterMapper $levelFilterMapper,
        private UserListMapper $mapper,
        private SeasonService $seasonService,
    ) {
    }

    public function getCollection(
        AbstractFilter $filter,
        FilterConfigInterface $filterConfig,
        HandlerContext $context,
    ): ListView {
        /** @var UserFilter $filter */
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

    public function initializeFilters(AbstractFilter $filter, ?HandlerContext $context): void
    {
        /** @var UserFilter $filter */
        $filter->setDefaultSeason($this->seasonService, $context->queryParams);
    }
}
