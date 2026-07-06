<?php

declare(strict_types=1);

namespace App\State\Skill\Provider;


use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\SkillFilter;
use App\Dto\View\ListView;
use App\Mapper\Skill\SkillListMapper;
use App\Repository\SkillRepository;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;
use App\State\ListProviderInterface;
use Doctrine\ORM\QueryBuilder;

class SkillListProvider implements ListProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private SkillRepository $skillRepository,
        private PaginatorService $paginator,
        private SkillListMapper $mapper,
    ) {
    }

    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, string $route, ?int $currentPage = 1): ListView
    {
        /** @var SkillFilter $filter */
        $qb = $this->getQueryBuilder($filter);

        $entities = $this->paginator->paginate(
            $qb,
            $currentPage,
            $filter->itemsPerPage ?? PaginatorService::PAGINATOR_PER_PAGE
        );

        return $this->mapper->mapToView(
            $entities,
            $route,
            $currentPage,
            $filter,
            $filterConfig
        );
    }

    private function getQueryBuilder(SkillFilter $filter): QueryBuilder
    {
        $qb = $this->skillRepository->getSkillQuery();

        if ($filter->category) {
            $this->skillRepository->filterCategory($qb, $filter->category);
        }

        if ($filter->content) {
            $this->skillRepository->filterContent($qb, $filter->content);
        }

        if ($filter->level) {
            $this->skillRepository->filterLevel($qb, $filter->level);
        }

        if (!$filter->showDeleted) {
            $this->skillRepository->filterActive($qb);
        }
        
        if ($filter->sort) {
            $this->skillRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }
}
