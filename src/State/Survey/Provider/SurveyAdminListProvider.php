<?php

declare(strict_types= 1);

namespace App\State\Survey\Provider;

use App\Dto\Filter\SurveyFilter;
use App\Dto\ListDto;
use App\Entity\Enum\SurveyStatusEnum;
use App\Mapper\Survey\SurveyAdminListMapper;
use App\Repository\SurveyRepository;
use App\Service\PaginatorService;
use DateTime;


class SurveyAdminListProvider
{
    public function __construct(
        private SurveyRepository $surveyRepository,
        private PaginatorService $paginator,
        private SurveyAdminListMapper $mapper,
    )
    {

    }
    
    public function getCollection(SurveyFilter $filter, string $route, ?int $currentPage = 1): ListDto
    {
        $today = (new DateTime())->setTime(0,0,0);
        $qb = $this->surveyRepository->findSurveyQuery();
        match($filter->status) {
            SurveyStatusEnum::PENDING => $this->surveyRepository->filterPending($qb, $today),
            SurveyStatusEnum::EXPIRED => $this->surveyRepository->filterExpired($qb, $today),
            SurveyStatusEnum::DISABLED => $this->surveyRepository->filterDisabled($qb),
            default => null
        };

        $entities = $this->paginator->paginate(
            $qb, 
            $currentPage, 
            PaginatorService::PAGINATOR_PER_PAGE
        );

        return $this->mapper->mapToView($entities, $route, $currentPage, $filter);
    }
}

