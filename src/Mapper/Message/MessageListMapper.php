<?php

declare(strict_types=1);

namespace App\Mapper\Message;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\MessageFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Entity\Enum\LevelType;
use App\Entity\Message;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MessageListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        HandlerContext $context,
        MessageFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $route = $context->route;
        $currentPage = $context->page;
        $targetUrl = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        $items = [];

        /** @var Message $entity */
        foreach ($entities as $entity) {
            $items[] = new ListItemView(
                id: sprintf('message-item-%s', $entity->getId()),
                labels: [
                    new LabelView($entity->getLabel()),
                ],
                indicators: $this->indicators($entity),
                dropdown: $this->dropDown($entity, $targetUrl),
                gridTemplateContent: 'grid-cols-[1fr_50px]',
            );
        }

        return new ListView(
            name: 'message',
            title: 'Messages',
            description: 'Administration des messages.',
            items: $items,
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            addItem: new LinkView(
                label: 'Ajouter un message',
                url: $this->urlContextService->generateUrl('admin_message_add', [], $targetUrl),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close')
                ],
            ),
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
        );
    }

    private function dropDown(Message $entity, string $targetUrl): DropdownView
    {
        $items = [
            new LinkView(
                label: 'Modifier',
                url: $this->urlContextService->generateUrl('admin_message_edit', ['message' => $entity->getId()], $targetUrl),
                icon: 'lucide:pencil',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close'),
                ],
            )
        ];

        if (!$entity->isProtected()) {
            $items[] = new LinkView(
                label: 'Supprimer',
                url: $this->urlContextService->generateUrl('admin_message_delete', ['message' => $entity->getId()], $targetUrl),
                icon: 'lucide:delete',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::MODAL_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close'),
                ],
            );
        }

        return  new DropdownView(
            menuItems: $items
        );
    }

    private function indicators(Message $message): array
    {
        $levelType = $message->getLevelType();
        return (LevelType::NONE !== $levelType)
            ? [
                new BadgeView(
                    value: $levelType->getIcon(),
                    variant: ColorVariant::ACCENT,
                    size: Size::ICON
                ),
            ]
            : [];
    }
}
