<?php

declare(strict_types=1);

namespace App\Mapper\Notification;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\NotificationFilter;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Dto\View\ListView;
use App\Entity\Notification;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationAdminListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UrlContextService $urlContextService,
        private PaginatorMapper $paginatorMapper,
        private FilterChipsMapper $filterChipsMapper,
        private NotificationMapper $notificationMapper,
    ) {
    }

    public function mapToView(Paginator $entities, HandlerContext $context, NotificationFilter $filter, FilterConfigInterface $filterConfig): ListView
    {
        $route = $context->route;
        $currentPage = $context->page;
        $targetUrl = $this->urlContextService->encodeUrl($route, $filter->toQueryParams($currentPage));

        return new ListView(
            name: 'notifications',
            title: 'Notification Pop\'up',
            description: 'Administration des messages affichés dans les pop\'up.',
            items: array_map(fn (Notification $notification) => $this->notificationMapper->mapToView($notification, $targetUrl), iterator_to_array($entities)),
            addItem: new LinkView(
                label: 'Ajouter une pop\'up',
                url: $this->urlContextService->generateUrl('admin_notification_add', [], $targetUrl),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                ],
            ),
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
        );
    }
}
