<?php

declare(strict_types=1);

namespace App\State\Coverage\Provider;

use App\Core\Contract\Provider\AutocompleteProviderInterface;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\CoverageFilter;
use App\Mapper\LevelFilterMapper;
use App\Mapper\User\UserAutocompleteMapper;
use App\Repository\MemberRepository;
use App\Service\SeasonService;
use App\State\Coverage\Trait\CoverageDataProviderTrait;

/**
 * @implements AutocompleteProviderInterface<CoverageFilter>
 */
class CoverageAutocompleteProvider implements AutocompleteProviderInterface
{
    use FilterHydratorTrait;
    use CoverageDataProviderTrait;

    public function __construct(
        private MemberRepository $memberRepository,
        private UserAutocompleteMapper $autocompleteMapper,
        private SeasonService $seasonService,
        private LevelFilterMapper $levelFilterMapper,
    ) {
    }

    public function getAutocompleteChoices(?string $query, ?AbstractFilter $filter): array
    {
        $qb = $this->getQueryBuilder($filter);
        $this->memberRepository->filterTerm($qb, $query);

        return $this->autocompleteMapper->mapToChoices($qb->getQuery()->getResult());
    }
}
