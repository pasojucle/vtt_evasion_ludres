<?php

declare(strict_types=1);

namespace App\State\SurveyIssue\Provider;

use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\SurveyIssueAutocompleteFilter;
use App\Mapper\SurveyIssue\SurveyIssueAutocompleteMapper;
use App\Repository\SurveyIssueRepository;
use Doctrine\ORM\QueryBuilder;

class SurveyIssueAutocompleteProvider
{
    use FilterHydratorTrait;
    
    public function __construct(
        private SurveyIssueRepository $surveyIssueRepository,
        private SurveyIssueAutocompleteMapper $mapper,
    ) {
    }
    
    public function getAutocompleteChoices(
        ?string $query,
        AbstractFilter $filter,
    ): array {
        /** @var SurveyIssueAutocompleteFilter $filter */
        return $this->mapper->mapToChoices(
            $this->getQueryBuilder($query, $filter)->getQuery()->getResult()
        );
    }

    private function getQueryBuilder(?string $query, SurveyIssueAutocompleteFilter $filter): QueryBuilder
    {
        $qb = $this->surveyIssueRepository->getSurveyIssueQuery();
    
        if ($filter->survey) {
            $this->surveyIssueRepository->filterSurveyy($qb, $filter->survey);
        }
        if ($query) {
            $this->surveyIssueRepository->filterContent($qb, $query);
        }

        return $qb;
    }
}
