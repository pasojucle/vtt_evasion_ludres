<?php

declare(strict_types=1);

namespace App\Mapper\Notification;

use App\Dto\BadgeDto;
use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\PublishStatus;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Filter\NotificationFilter;
use App\Dto\HtmlAttributDto;
use App\Dto\LabelDto;
use App\Dto\ListDto;
use App\Dto\ListItemDto;
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

    public function mapToView(Paginator $entities, string $route, int $currentPage, NotificationFilter $filter, FilterConfigInterface $filterConfig): ListDto
    {
        $items = [];
        /** @var Notification $entity */
        foreach ($entities as $entity) {
            $status = $entity->isDisabled() ? PublishStatus::DISABLED : PublishStatus::ENABLED;
            $items[] = new ListItemDto(
                labels: [
                    new LabelDto($entity->getTitle()),
                    new LabelDto(sprintf('Du %s au %s', $entity->getStartAt()->format('d/m/y'), $entity->getEndAt()->format('d/m/y'))),
                ],
                status: new BadgeDto($status->trans($this->translator), $status->variant()),
                dropdown: $this->getDropdown($entity),
                url: $this->urlGenerator->generate("admin_order", ['orderHeader' => $entity->getId()]),
            );
        }

        return new ListDto(
            items: $items,
            addItem: new ButtonDto(
                label: 'Ajouter une pop\'up',
                url: $this->urlGenerator->generate('admin_notification_add'),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
            ),
            settings: $this->dropdownSettingsMapper->mapToView('ORDER', RoundedVariant::ROUNDED_END),
            advancedFilter: new ButtonDto(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::SHEET_CONTENT),
                    new HtmlAttributDto('data-action', 'click->dropdown#close')
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            paginator: $this->paginatorMapper->fromEntities($entities, $route, $currentPage, $filter),
        );
    }

    private function getDropdown(Notification $enity): DropdownDto
    {
        $menuItems = [];
        if ($enity->isDisabled()) {
            $menuItems[] = new ButtonDto(
                label: 'Activer',
                url: $this->urlGenerator->generate('admin_notification_toggle_disable', ['notification' => $enity->getId()]),
                icon: 'lucide:toggle-left',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                    new HtmlAttributDto('data-action', 'click->dropdown#close'),
                ],
            );
        } else {
            $menuItems[] = new ButtonDto(
                label: 'Désactiver',
                url: $this->urlGenerator->generate('admin_notification_toggle_disable', ['notification' => $enity->getId()]),
                icon: 'lucide:toggle-right',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                    new HtmlAttributDto('data-action', 'click->dropdown#close'),
                ],
            );
        }

        return new DropdownDto(
            menuItems: $menuItems,
        );
    }
}
