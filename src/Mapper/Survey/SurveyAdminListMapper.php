<?php

declare(strict_types=1);

namespace App\Mapper\Survey;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\SurveyFilter;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Dto\View\ListView;
use App\Entity\Survey;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SurveyAdminListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UrlContextService $urlContextService,
        private PaginatorMapper $paginatorMapper,
        private FilterChipsMapper $filterChipsMapper,
        private SurveyMapper $surveyMapper,
    ) {
    }

    public function mapToView(Paginator $entities, HandlerContext $context, SurveyFilter $filter, FilterConfigInterface $filterConfig): ListView
    {
        $route = $context->route;
        $currentPage = $context->page;
        $queries = $filter->toQueryParams($currentPage);
        $targetUrl = $this->urlContextService->encodeUrl($route, $queries);
      
        return new ListView(
            name: 'survey',
            title: 'Sondages',
            description: 'Administration des sondages : création des questionnaires et suivi des réponses.',
            items: array_map(fn (Survey $survey) => $this->surveyMapper->mapToView($survey, $targetUrl), iterator_to_array($entities)),
            advancedFilter: new LinkView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close')
                ],
            ),
            filterChipViews: $this->filterChipsMapper->mapToView($filter, $filterConfig->getAdvancedFields(), $filterConfig->getRouteName()),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            addItem: new LinkView(
                label: 'Ajouter un sondage',
                url: $this->urlContextService->generateUrl('admin_survey_add', [], $targetUrl),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close')
                ],
            ),
        );
    }
}
