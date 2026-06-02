<?php

declare(strict_types=1);

namespace App\State\User\Provider;

use App\Dto\ListDto;
use App\Dto\Filter\UserFilter;
use App\Mapper\EmailClipboardMapper;
use App\Mapper\LevelFilterMapper;
use App\Mapper\User\UserListExportMapper;
use App\Mapper\User\UserAutocompleteMapper;
use App\Mapper\User\UserListMapper;
use App\Repository\MemberRepository;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;
use Doctrine\ORM\QueryBuilder;

class UserListProvider
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
    ) {
    }

    public function getCollection(UserFilter $filter, FilterConfigInterface $filterConfig, string $route, ?int $currentPage = 1): ListDto
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

    public function streamExportContent(UserFilter $filter): void
    {
        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        $this->exportMapper->streamToCsv($entities);
    }

    public function copyEmailListToClipboard(UserFilter $filter): string
    {
        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        return $this->emailClipboardMapper->mapToEmailCsvString($entities);
    }

    public function getAutocompleteChoices(UserFilter $filter): array
    {
        $qb = $this->getQueryBuilder($filter);

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