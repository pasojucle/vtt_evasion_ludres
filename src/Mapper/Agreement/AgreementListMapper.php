<?php

declare(strict_types=1);

namespace App\Mapper\Agreement;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\AgreementFilter;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Dto\View\ListView;
use App\Entity\Agreement;
use App\Mapper\Agreement\AgreementMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AgreementListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private AgreementMapper $agreementMapper,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        HandlerContext $context,
        AgreementFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $route = $context->route;
        $currentPage = $context->page;
        $targetUrl = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        return new ListView(
            name: 'agreement',
            title: 'Autorisations',
            description: 'Administration des autorisations.',
            items: array_map(fn (Agreement $agreement) => $this->agreementMapper->mapToView($agreement, $targetUrl), iterator_to_array($entities)),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            advancedFilter: new LinkView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close'),
                ],
            ),
            filterChipViews: $this->filterChipsMapper->mapToView($filter, $filterConfig->getAdvancedFields(), $filterConfig->getRouteName()),
            addItem: new LinkView(
                label: 'Ajouter une autorisation',
                url: $this->urlContextService->generateUrl('admin_agreement_add', [], $targetUrl),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close'),
                ],
            ),
            sortableUrl: $this->urlContextService->generateUrl('admin_agreement_order', ['agreement' => 0], $targetUrl),
        );
    }
}
