<?php

declare(strict_types=1);

namespace App\State\Survey\Provider;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Contract\Provider\ListProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\SurveyFilter;
use App\Dto\View\ListView;
use App\Entity\Enum\SurveyRestriction;
use App\Entity\Enum\SurveyStatusEnum;
use App\Mapper\EmailClipboardMapper;
use App\Mapper\Survey\SurveyAdminListMapper;
use App\Repository\SurveyRepository;
use App\Service\PaginatorService;
use DateTime;
use Doctrine\ORM\QueryBuilder;

class SurveyAdminListProvider implements ListProviderInterface
{
    use FilterHydratorTrait;
    
    public function __construct(
        private SurveyRepository $surveyRepository,
        private PaginatorService $paginator,
        private SurveyAdminListMapper $mapper,
        private EmailClipboardMapper $emailClipboardMapper
    ) {
    }
    
    public function getCollection(
        AbstractFilter $filter,
        FilterConfigInterface $filterConfig,
        HandlerContext $context,
    ): ListView {
        /** @var SurveyFilter $filter */
        $qb = $this->getQueryBuilder($filter);

        $entities = $this->paginator->paginate(
            $qb,
            $context->page,
            $filter->itemsPerPage ?? PaginatorService::PAGINATOR_PER_PAGE
        );

        return $this->mapper->mapToView(
            $entities,
            $context,
            $filter,
            $filterConfig
        );
    }

    public function copyEmailListToClipboard(SurveyFilter $filter): string
    {
        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        return $this->emailClipboardMapper->mapToEmailCsvString($entities);
    }
    
    private function getQueryBuilder(SurveyFilter $filter): QueryBuilder
    {
        $today = (new DateTime())->setTime(0, 0, 0);
        $qb = $this->surveyRepository->findSurveyQuery();
        match ($filter->status) {
            SurveyStatusEnum::PENDING => $this->surveyRepository->filterPending($qb, $today),
            SurveyStatusEnum::EXPIRED => $this->surveyRepository->filterExpired($qb, $today),
            SurveyStatusEnum::DISABLED => $this->surveyRepository->filterDisabled($qb),
            default => null
        };

        if ($filter->restriction) {
            match ($filter->restriction) {
                SurveyRestriction::MEMBERS => $this->surveyRepository->filterHasMembers($qb),
                SurveyRestriction::ACTIVITY => $this->surveyRepository->filterHasActivity($qb),
                default => null,
            };
        }

        if ($filter->sort) {
            $this->surveyRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }
}
