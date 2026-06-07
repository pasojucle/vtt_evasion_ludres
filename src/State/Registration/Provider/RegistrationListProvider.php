<?php

declare(strict_types=1);

namespace App\State\Registration\Provider;

use App\Dto\Enum\RegistrationStatus;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\RegistrationFilter;
use App\Dto\ListDto;
use App\Mapper\EmailClipboardMapper;
use App\Mapper\LevelFilterMapper;
use App\Mapper\Registration\RegistrationListMapper;
use App\Mapper\User\UserListExportMapper;
use App\Mapper\User\UserAutocompleteMapper;
use App\Repository\MemberRepository;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use App\Service\SeasonService;
use App\State\FilterHydratorTrait;
use App\State\ListProviderInterface;
use Doctrine\ORM\QueryBuilder;

class RegistrationListProvider implements ListProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private PaginatorService $paginator,
        private LevelFilterMapper $levelFilterMapper,
        private EmailClipboardMapper $emailClipboardMapper,
        private UserListExportMapper $exportMapper,
        private UserAutocompleteMapper $autocompleteMapper,
        private RegistrationListMapper $mapper,
        private MemberRepository $memberRepository,
        private SeasonService $seasonService,
    ) {
    }

    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, string $route, ?int $currentPage = 1): ListDto
    {
        assert($filter instanceof RegistrationFilter);

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

    public function getAutocompleteChoices(RegistrationFilter $filter): array
    {
        $qb = $this->getQueryBuilder($filter);

        return $this->autocompleteMapper->mapToChoices($qb->getQuery()->getResult());
    }

    public function streamExportContent(AbstractFilter $filter): void
    {
        assert($filter instanceof RegistrationFilter);

        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        $this->exportMapper->streamToCsv($entities);
    }

    public function copyEmailListToClipboard(RegistrationFilter $filter): string
    {
        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        return $this->emailClipboardMapper->mapToEmailCsvString($entities);
    }
    private function getQueryBuilder(RegistrationFilter $filter): QueryBuilder
    {
        $qb = $this->memberRepository->getMemberQuery();

        $currentSeason = $this->seasonService->getCurrentSeason();
        match($filter->status) {
            RegistrationStatus::TESTING_IN_PROGRESS => $this->memberRepository->filterTestinInProgress($qb, $currentSeason),
            RegistrationStatus::TESTING_COMPLETE => $this->memberRepository->filterTestinComplete($qb, $currentSeason),
            RegistrationStatus::NEW => $this->memberRepository->filterNew($qb, $currentSeason),
            RegistrationStatus::RENEW => $this->memberRepository->filterRenew($qb, $currentSeason),
            RegistrationStatus::WAITING_RENEW => $this->memberRepository->filterWaitingRenew($qb, $currentSeason),
            RegistrationStatus::IN_PROCESSING => $this->memberRepository->filterInProcessing($qb, $currentSeason),
            RegistrationStatus::TO_REGISTER => $this->memberRepository->filterToRegister($qb, $currentSeason),
        };

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
}
