<?php

declare(strict_types=1);

namespace App\Mapper\Notification;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\NotificationFilter;
use App\Dto\View\LinkView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\Interface\ListActionViewInterface;
use App\Dto\View\LabelView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Dto\View\ToggleStatusView;
use App\Entity\Notification;
use App\Mapper\FilterChipsMapper;
use App\Mapper\Notification\NotificationStatusMapper;
use App\Mapper\PaginatorMapper;
use App\Service\CsrfTokenService;
use App\Service\Filter\FilterConfigInterface;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationAdminListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private PaginatorMapper $paginatorMapper,
        private NotificationStatusMapper $notificationStatusMapper,
        private FilterChipsMapper $filterChipsMapper,
        private UrlContextService $urlContextService,
        private CsrfTokenService $csrfTokenService,
    ) {
    }

    public function mapToView(Paginator $entities, string $route, int $currentPage, NotificationFilter $filter, FilterConfigInterface $filterConfig): ListView
    {
        $referer = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));
        $items = [];
        /** @var Notification $entity */
        foreach ($entities as $entity) {
            $tokenId = $this->csrfTokenService->getTokenId($entity);

            $items[] = new ListItemView(
                labels: [
                    new LabelView($entity->getTitle()),
                    new LabelView(sprintf('Du %s au %s', $entity->getStartAt()->format('d/m/y'), $entity->getEndAt()->format('d/m/y'))),
                ],
                status: $this->notificationStatusMapper->mapToView($entity, $tokenId),
                action: $this->getAction($entity, $tokenId),
                url: $this->urlGenerator->generate("admin_order", ['orderHeader' => $entity->getId()]),
                gridTemplateRow: 'grid-cols-[1fr_50px]',
                gridTemplateContent: 'grid-cols-1 lg:grid-cols-[3fr_1fr]',
                gridTemplateBadges: 'grid-cols-[fr_70px]',
                gridTemplateLabels: 'grid-cols-1 lg:grid-cols-[2fr_1fr]',
            );
        }

        return new ListView(
            name: 'notifications',
            title: 'Notification Pop\'up',
            description: 'Administration des messages affichés dans les pop\'up.',
            items: $items,
            addItem: new LinkView(
                label: 'Ajouter une pop\'up',
                url: $this->urlGenerator->generate('admin_notification_add'),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
            ),
            advancedFilter: new LinkView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close')
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
        );
    }

    private function getAction(Notification $entity, string $toggleStatusId): ListActionViewInterface
    {
        return new ToggleStatusView(
            url: $this->urlGenerator->generate('admin_notification_toggle', ['notification' => $entity->getId()]),
            tokenId: $toggleStatusId,
            isActive: !$entity->isDisabled(),
        );
    }
}
