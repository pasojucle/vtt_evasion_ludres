<?php

declare(strict_types=1);

namespace App\State\SurveyResponse\Provider;

use App\Core\Contract\Provider\FilterInitializerInterface;
use App\Core\Contract\Provider\ListDrawerProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Enum\SurveyResponseValueType;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\SurveyResponseFilter;
use App\Dto\View\ListDrawerView;
use App\Entity\Survey;
use App\Mapper\SurveyResponse\SurveyResponseIssueReadMapper;
use App\Repository\SurveyResponseRepository;
use App\Service\UrlContextService;
use Doctrine\ORM\QueryBuilder;

/**
 * @implements ListDrawerProviderInterface<SurveyResponseFilter>
 */
class SurveyResponcesReadProvider implements ListDrawerProviderInterface, FilterInitializerInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private SurveyResponseIssueReadMapper $surveyResponseIssueReadMapper,
        private SurveyResponseRepository $surveyresponseRepository,
        private UrlContextService $urlContextService,
    ) {
    }

    public function getCollection(AbstractFilter $filter, HandlerContext $context): ListDrawerView
    {
        $qb = $this->getQueryBuilder($filter);
        
        return $this->surveyResponseIssueReadMapper->mapToView(
            $context->parent,
            $qb->getQuery()->getResult(),
            $this->urlContextService->decodeUrl($context->encodedFallback),
        );
    }

    public function initializeFilters(AbstractFilter $filter, ?HandlerContext $context): void
    {
        /** @var SurveyResponseFilter $filter */
        $filter->setSurvey($context->parent);
    }


    private function getQueryBuilder(SurveyResponseFilter $filter): QueryBuilder
    {
        $qb = $this->surveyresponseRepository->getSurveyResponseQuery();

        if ($filter->issue) {
            $this->surveyresponseRepository->filterIssue($qb, $filter->issue);
        }

        if ($filter->value) {
            dump($filter->value);
            $valueRaw = match ($filter->value) {
                SurveyResponseValueType::YES => true,
                SurveyResponseValueType::NO => false,
                SurveyResponseValueType::NO_OPINION => null,
                default => $filter->value
            };

            if (!$valueRaw instanceof SurveyResponseValueType) {
                $this->surveyresponseRepository->filterValue($qb, $valueRaw);
            }
        }

        return $qb;
    }
}
