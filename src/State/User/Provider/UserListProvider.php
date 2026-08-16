<?php

declare(strict_types=1);

namespace App\State\User\Provider;

use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\UserFilter;
use App\Dto\View\ListView;
use App\Mapper\EmailClipboardMapper;
use App\Mapper\LevelFilterMapper;
use App\Mapper\User\UserAutocompleteMapper;
use App\Mapper\User\UserListExportMapper;
use App\Mapper\User\UserListMapper;
use App\Repository\MemberRepository;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use App\Service\SeasonService;
use App\State\FilterHydratorTrait;
use App\State\Interface\FilterInitializerInterface;
use App\State\Interface\ListProviderInterface;
use App\State\Interface\StreamExportableInterface;
use Doctrine\ORM\QueryBuilder;

class UserListProvider implements ListProviderInterface, FilterInitializerInterface, StreamExportableInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private MemberRepository $memberRepository,
        private PaginatorService $paginator,
        private LevelFilterMapper $levelFilterMapper,
        private UserListMapper $mapper,
        private UserAutocompleteMapper $autocompleteMapper,
        private UserListExportMapper $exportMapper,
        private EmailClipboardMapper $emailClipboardMapper,
        private SeasonService $seasonService,
    ) {
    }

    public function getCollection(
        AbstractFilter $filter,
        FilterConfigInterface $filterConfig,
        string $route,
        ?int $currentPage = 1,
    ): ListView {
        /** @var UserFilter $filter */
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

    public function initializeFilters(AbstractFilter $filter, array $queryParams = []): void
    {
        /** @var UserFilter $filter */
        $filter->setDefaultSeason($this->seasonService, $queryParams);
    }

    public function streamExportContent(AbstractFilter $filter): void
    {
        /** @var UserFilter $filter */
        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        $this->exportMapper->streamToCsv($entities);
    }

    public function copyEmailListToClipboard(UserFilter $filter): string
    {
        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        return $this->emailClipboardMapper->mapToEmailCsvString($entities);
    }

    public function getAutocompleteChoices(string $query, UserFilter $filter): array
    {
        $qb = $this->getQueryBuilder($filter);
        $this->memberRepository->filterTerm($qb, $query);

        return $this->autocompleteMapper->mapToChoices($qb->getQuery()->getResult());
    }

    private function getQueryBuilder(UserFilter $filter): QueryBuilder
    {
        $qb = $this->memberRepository->getMemberQuery();

        if ($filter->member) {
            $this->memberRepository->filterMember($qb, $filter->member->getId());
        }

        if (is_int($filter->season)) {
            $this->memberRepository->filterSeason($qb, $filter->season);
        }

        if ($filter->isBoardMember) {
            $this->memberRepository->filterBoardMember($qb, $filter->isBoardMember);
        }

        if (!empty($filter->levels)) {
            [$levelTypes, $levels] = $this->levelFilterMapper->parseRawLevels($filter->levels);
            $this->memberRepository->filterLevels($qb, $levelTypes, $levels);
        }

        if ($filter->permissions) {
            $this->memberRepository->filterPermission($qb, $filter->permissions);
        }

        if ($filter->sort) {
            $this->memberRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }
}
