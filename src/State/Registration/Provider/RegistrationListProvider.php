<?php

declare(strict_types=1);

namespace App\State\Registration\Provider;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Contract\Provider\ListProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\RegistrationFilter;
use App\Dto\View\ListView;
use App\Mapper\LevelFilterMapper;
use App\Mapper\Registration\RegistrationListMapper;
use App\Repository\MemberRepository;
use App\Service\PaginatorService;
use App\Service\SeasonService;
use App\State\Registration\Trait\RegistrationListDataTrait;

class RegistrationListProvider implements ListProviderInterface
{
    use FilterHydratorTrait;
    use RegistrationListDataTrait;

    public function __construct(
        private PaginatorService $paginator,
        private LevelFilterMapper $levelFilterMapper,
        private RegistrationListMapper $mapper,
        private MemberRepository $memberRepository,
        private SeasonService $seasonService,
    ) {
    }

    public function getCollection(
        AbstractFilter $filter,
        FilterConfigInterface $filterConfig,
        HandlerContext $context,
    ): ListView {
        /** @var RegistrationFilter $filter */

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
