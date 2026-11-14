<?php

declare(strict_types=1);

namespace App\Mapper\SecondHandCategory;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemView;
use App\Entity\SecondHandCategory;
use App\Service\UrlContextService;

class SecondHandCategoryMapper
{
    public function __construct(
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        SecondHandCategory $category,
        string $targetUrl,
    ): ListItemView {
        return new ListItemView(
            id: sprintf('secondhand-category-item-%s', $category->getId()),
            labels: [
                new LabelView($category->getName()),
            ],
            status: $this->getStatus($category),
            isDeleted: $category->isDeleted(),
            indicators: $this->getIndicators($category),
            dropdown: $this->dropDown($category, $targetUrl),
            gridTemplateContent: 'grid-cols-[1fr_80px] lg:grid-cols-[1fr_150px]',
            gridTemplateBadges: 'grid-cols-1 lg:grid-cols-[1fr_2fr] gap-2 justify-items-center',
        );
    }

    private function getIndicators(SecondHandCategory $category): array
    {
        return [
            new BadgeView(
                value: $category->getIcon(),
                variant: ColorVariant::ACCENT,
                size: Size::ICON
            )
        ];
    }

    private function dropDown(SecondHandCategory $category, string $referer): DropdownView
    {
        if ($category->isDeleted()) {
            return new DropdownView(
                menuItems: [
                    new LinkView(
                        label: 'Restaurer',
                        url: $this->urlContextService->generateUrl('admin_second_hand_category_restore', [
                            'category' => $category->getId()
                            ], $referer),
                        icon: 'lucide:archive-restore',
                        variant: ColorVariant::DROPDOWN,
                    ),
                ]
            );
        }
        return  new DropdownView(
            menuItems: [
                new LinkView(
                    label: 'Modifier',
                    url: $this->urlContextService->generateUrl('admin_second_hand_category_edit', ['category' => $category->getId()], $referer),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                        new HtmlAttributeView('data-action', 'click->dropdown#close'),
                    ],
                ),
                 new LinkView(
                     label: 'Supprimer',
                     url: $this->urlContextService->generateUrl('admin_second_hand_category_delete', ['category' => $category->getId()], $referer),
                     icon: 'lucide:delete',
                     variant: ColorVariant::DROPDOWN,
                     htmlAttributes: [
                        new HtmlAttributeView('data-turbo-frame', LinkView::MODAL_CONTENT),
                        new HtmlAttributeView('data-action', 'click->dropdown#close'),
                    ],
                 )
            ]
        );
    }

    private function getStatus(SecondHandCategory $category): ?BadgeView
    {
        if ($category->isDeleted()) {
            return new BadgeView('Supprimée', ColorVariant::DESTRUCTIVE);
        }

        return null;
    }
}
