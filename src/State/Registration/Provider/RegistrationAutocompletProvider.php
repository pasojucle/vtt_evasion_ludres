<?php

declare(strict_types=1);

namespace App\State\Registration\Provider;

use App\Core\Contract\Provider\AutocompleteProviderInterface;
use App\Core\Contract\Provider\ListFilteredProviderInterface;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\RegistrationFilter;
use App\Mapper\LevelFilterMapper;
use App\Mapper\User\UserAutocompleteMapper;
use App\Repository\MemberRepository;
use App\Service\SeasonService;
use App\State\Registration\Trait\RegistrationListDataTrait;

/**
 * @implements AutocompleteProviderInterface<RegistrationFilter>
 */
class RegistrationAutocompletProvider implements AutocompleteProviderInterface, ListFilteredProviderInterface
{
    use FilterHydratorTrait;
    use RegistrationListDataTrait;

    public function __construct(
        private LevelFilterMapper $levelFilterMapper,
        private UserAutocompleteMapper $autocompleteMapper,
        private MemberRepository $memberRepository,
        private SeasonService $seasonService,
    ) {
    }

    public function getAutocompleteChoices(?string $query, ?AbstractFilter $filter): array
    {
        $qb = $this->getQueryBuilder($filter);
        $this->memberRepository->filterTerm($qb, $query);

        return $this->autocompleteMapper->mapToChoices($qb->getQuery()->getResult());
    }
}
