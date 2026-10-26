<?php

declare(strict_types=1);

namespace App\State\Skill\Provider;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Contract\Provider\ListProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\SkillFilter;
use App\Dto\View\ListView;
use App\Mapper\Skill\SkillListMapper;
use App\Repository\SkillRepository;
use App\Service\PaginatorService;
use App\State\Skill\Trait\SkillDataProviderTrait;

class SkillListProvider implements ListProviderInterface
{
    use FilterHydratorTrait;
    use SkillDataProviderTrait;

    public function __construct(
        private SkillRepository $skillRepository,
        private PaginatorService $paginator,
        private SkillListMapper $mapper,
    ) {
    }

    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, ?HandlerContext $context = null): ListView
    {
        /** @var SkillFilter $filter */
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
