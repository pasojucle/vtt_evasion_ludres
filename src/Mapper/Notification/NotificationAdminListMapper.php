<?php

declare(strict_types=1);

namespace App\Mapper\Notification;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\PublishStatus;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Filter\NotificationFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\ButtonView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LabelView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Entity\Notification;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\Filter\FilterConfigInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationAdminListMapper
{
    public function __construct(
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private PaginatorMapper $paginatorMapper,
        private FilterChipsMapper $filterChipsMapper,
    ) {
    }

    public function mapToView(Paginator $entities, string $route, int $currentPage, NotificationFilter $filter, FilterConfigInterface $filterConfig): ListView
    {
        $items = [];
        /** @var Notification $entity */
        foreach ($entities as $entity) {
            $status = $entity->isDisabled() ? PublishStatus::DISABLED : PublishStatus::ENABLED;
            $items[] = new ListItemView(
                labels: [
                    new LabelView($entity->getTitle()),
                    new LabelView(sprintf('Du %s au %s', $entity->getStartAt()->format('d/m/y'), $entity->getEndAt()->format('d/m/y'))),
                ],
                status: new BadgeView($status->trans($this->translator), $status->variant()),
                dropdown: $this->getDropdown($entity),
                url: $this->urlGenerator->generate("admin_order", ['orderHeader' => $entity->getId()]),
                gridTemplateContent:' grid-cols-1 lg:grid-cols-[3fr_1fr]',
                gridTemplateLabels: 'grid-cols-1 lg:grid-cols-[2fr_1fr]',
            );
        }

        return new ListView(
            id: 'notifications_container',
            title: 'Notification Pop\'up',
            description: 'Administration des messages affichés dans les pop\'up.',
            items: $items,
            addItem: new ButtonView(
                label: 'Ajouter une pop\'up',
                url: $this->urlGenerator->generate('admin_notification_add'),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
            ),
            settings: $this->dropdownSettingsMapper->mapToView('ORDER', RoundedVariant::ROUNDED),
            advancedFilter: new ButtonView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close')
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
        );
    }

    private function getDropdown(Notification $enity): DropdownView
    {
        $menuItems = [];
        if ($enity->isDisabled()) {
            $menuItems[] = new ButtonView(
                label: 'Activer',
                url: $this->urlGenerator->generate('admin_notification_toggle_disable', ['notification' => $enity->getId()]),
                icon: 'lucide:toggle-left',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::MODAL_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close'),
                ],
            );
        } else {
            $menuItems[] = new ButtonView(
                label: 'Désactiver',
                url: $this->urlGenerator->generate('admin_notification_toggle_disable', ['notification' => $enity->getId()]),
                icon: 'lucide:toggle-right',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::MODAL_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close'),
                ],
            );
        }

        return new DropdownView(
            menuItems: $menuItems,
        );
    }
}
