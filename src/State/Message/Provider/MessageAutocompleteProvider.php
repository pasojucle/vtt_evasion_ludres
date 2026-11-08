<?php

declare(strict_types=1);

namespace App\State\Message\Provider;

use App\Core\Contract\Provider\AutocompleteProviderInterface;
use App\Core\Contract\Provider\ListFilteredProviderInterface;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\MessageAutocompleteFilter;
use App\Mapper\Activity\ActivityAutocompleteMapper;
use App\Repository\MessageRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * @implements AutocompleteProviderInterface<MessageAutocompleteFilter>
 */
class MessageAutocompleteProvider implements AutocompleteProviderInterface, ListFilteredProviderInterface
{
    use FilterHydratorTrait;

    
    public function __construct(
        private MessageRepository $messageRepository,
        private ActivityAutocompleteMapper $activityAutocompleteMapper,
    ) {
    }

    public function getAutocompleteChoices(?string $query, ?AbstractFilter $filter): array
    {
        $activities = $this->getQueryBuilder($query, $filter)->getQuery()->getResult();

        return $this->activityAutocompleteMapper->mapToChoices($activities);
    }

    private function getQueryBuilder(?string $query, MessageAutocompleteFilter $filter): QueryBuilder
    {
        $qb = $this->messageRepository->getMessageQuery();
        if ($query) {
            $this->messageRepository->filterLabel($qb, $query);
        }

        if ($filter->section) {
            $this->messageRepository->filterSection($qb, $filter->section);
        };

        return $qb;
    }
}
