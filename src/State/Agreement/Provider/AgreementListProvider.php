<?php

declare(strict_types=1);

namespace App\State\Agreement\Provider;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Contract\Provider\ListProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\AgreementFilter;
use App\Dto\View\ListView;
use App\Mapper\Agreement\AgreementListMapper;
use App\Repository\AgreementRepository;
use App\Service\PaginatorService;
use Doctrine\ORM\QueryBuilder;

/**
 * @implements ListProviderInterface<AgreementFilter>
 */
class AgreementListProvider implements ListProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private AgreementRepository $agreementRepository,
        private PaginatorService $paginator,
        private AgreementListMapper $mapper,
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

    private function getQueryBuilder(AgreementFilter $filter): QueryBuilder
    {
        $qb = $this->agreementRepository->getAgreementQuery();

        if ($filter->name) {
            $this->agreementRepository->filterName($qb, $filter->name);
        }

        return $qb;
    }
}
