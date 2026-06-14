<?= '<?php' ?>

declare(strict_types=1);

namespace App\State\<?= $entity_name ?>\Provider;

use App\Dto\View\ListView;
use App\Dto\Filter\<?= $entity_name ?>Filter;
use App\Mapper\<?= $entity_name ?>\<?= $entity_name ?>ListMapper;
use App\Repository\<?= $entity_name ?>Repository;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;
use Doctrine\ORM\QueryBuilder;


class <?= $entity_name ?>ListProvider
{
    use FilterHydratorTrait;

    public function __construct(
        private <?= $entity_name ?>Repository $<?= lcfirst($entity_name) ?>Repository,
        private PaginatorService $paginator,
        private <?= $entity_name ?>ListMapper $mapper,
    ) {
    }

    public function getCollection(<?= $entity_name ?>Filter $filter, FilterConfigInterface $filterConfig, string $route, ?int $currentPage = 1): ListView
    {
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

    private function getQueryBuilder(UserFilter $filter): QueryBuilder
    {
        $qb = $this-><?= lcfirst($entity_name) ?>Repository->find<?= $entity_name ?>Query();
        
        // TODO: Ajoutez les filtres spécifiques à votre entité ici

        if ($filter->sort) {
            $this-><?= lcfirst($entity_name) ?>Repository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }
}