<?php

declare(strict_types=1);

namespace App\State\SkillCategory\Provider;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Contract\Provider\ListProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\SkillCategoryFilter;
use App\Dto\View\ListView;
use App\Mapper\SkillCategory\SkillCategoryListMapper;
use App\Repository\SkillCategoryRepository;
use App\Service\PaginatorService;
use Doctrine\ORM\QueryBuilder;

class SkillCategoryListProvider implements ListProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private SkillCategoryRepository $skillCategoryRepository,
        private PaginatorService $paginator,
        private SkillCategoryListMapper $mapper,
    ) {
    }

    public function getCollection(
        AbstractFilter $filter,
        FilterConfigInterface $filterConfig,
        HandlerContext $context,
    ): ListView {
        /** @var SkillCategoryFilter $filter */
        $qb = $this->getQueryBuilder($filter);

        $entities = $this->paginator->paginate(
            $qb,
            $$context->page,
            $filter->itemsPerPage ?? PaginatorService::PAGINATOR_PER_PAGE
        );

        return $this->mapper->mapToView(
            $entities,
            $context,
            $filter,
            $filterConfig
        );
    }

    private function getQueryBuilder(SkillCategoryFilter $filter): QueryBuilder
    {
        $qb = $this->skillCategoryRepository->getSkillCategoryQuery();

        if ($filter->name) {
            $this->skillCategoryRepository->filterName($qb, $filter->name);
        }

        if (!$filter->showDeleted) {
            $this->skillCategoryRepository->filterActive($qb);
        }

        if ($filter->sort) {
            $this->skillCategoryRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }
}
