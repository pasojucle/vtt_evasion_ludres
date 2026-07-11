<?php

declare(strict_types=1);

namespace App\State\Coverage\Provider;

use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\CoverageFilter;
use App\Dto\View\ListView;
use App\Mapper\Coverage\CoverageListMapper;
use App\Mapper\EmailClipboardMapper;
use App\Mapper\LevelFilterMapper;
use App\Mapper\User\UserAutocompleteMapper;
use App\Mapper\User\UserListExportMapper;
use App\Repository\MemberRepository;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use App\Service\SeasonService;
use App\State\FilterHydratorTrait;
use App\State\Interface\ListProviderInterface;
use App\State\Interface\StreamExportableInterface;
use Doctrine\ORM\QueryBuilder;

class CoverageListProvider implements ListProviderInterface, StreamExportableInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private MemberRepository $memberRepository,
        private PaginatorService $paginator,
        private CoverageListMapper $mapper,
        private LevelFilterMapper $levelFilterMapper,
        private UserAutocompleteMapper $autocompleteMapper,
        private EmailClipboardMapper $emailClipboardMapper,
        private UserListExportMapper $exportMapper,
        private SeasonService $seasonService,
    ) {
    }

    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, string $route, ?int $currentPage = 1): ListView
    {
        /**  @var CoverageFilter $filter*/
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

    public function getAutocompleteChoices(CoverageFilter $filter): array
    {
        $qb = $this->getQueryBuilder($filter);

        return $this->autocompleteMapper->mapToChoices($qb->getQuery()->getResult());
    }

    public function copyEmailListToClipboard(CoverageFilter $filter): string
    {
        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        return $this->emailClipboardMapper->mapToEmailCsvString($entities);
    }
    public function streamExportContent(AbstractFilter $filter): void
    {
        /** @var CoverageFilter $filter */

        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        $this->exportMapper->streamToCsv($entities);
    }

    private function getQueryBuilder(CoverageFilter $filter): QueryBuilder
    {
        $qb = $this->memberRepository->getMemberQuery();
        $this->memberRepository->filterSeason($qb, $this->seasonService->getCurrentSeason());
        $this->memberRepository->filterNotValidate($qb);

        if ($filter->member) {
            $this->memberRepository->filterMember($qb, $filter->member->getId());
        }

        if (!empty($filter->levels)) {
            [$levelTypes, $levels] = $this->levelFilterMapper->parseRawLevels($filter->levels);
            $this->memberRepository->filterLevels($qb, $levelTypes, $levels);
        }

        if ($filter->sort) {
            $this->memberRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }

    public function filterNotValidate(QueryBuilder &$qb): void
    {
        $qb->andWhere(
            $qb->expr()->eq('li.currentSeasonForm', ':currentSeasonForm')
        )
            ->setParameter('currentSeasonForm', false)
        ;
    }
}
