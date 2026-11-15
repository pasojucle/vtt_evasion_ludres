<?php

declare(strict_types=1);

namespace App\State\User\Provider;

use App\Core\Contract\Provider\AutocompleteProviderInterface;
use App\Core\Contract\Provider\ListFilteredProviderInterface;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\UserFilter;
use App\Mapper\LevelFilterMapper;
use App\Mapper\User\UserAutocompleteMapper;
use App\Repository\MemberRepository;
use App\State\User\Trait\UserListDataTrait;

/**
 * @implements AutocompleteProviderInterface<UserFilter>
 */
class UserAutocompleteProvider implements AutocompleteProviderInterface, ListFilteredProviderInterface
{
    use FilterHydratorTrait;
    use UserListDataTrait;

    public function __construct(
        private MemberRepository $memberRepository,
        private LevelFilterMapper $levelFilterMapper,
        private UserAutocompleteMapper $autocompleteMapper,
    ) {
    }
    public function getAutocompleteChoices(?string $query, ?AbstractFilter $filter): array
    {
        $qb = $this->getQueryBuilder($filter);
        $this->memberRepository->filterTerm($qb, $query);

        return $this->autocompleteMapper->mapToChoices($qb->getQuery()->getResult());
    }
}
