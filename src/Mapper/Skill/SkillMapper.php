<?php

declare(strict_types=1);

namespace App\Mapper\Skill;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemView;
use App\Entity\Skill;
use App\Service\UrlContextService;

class SkillMapper
{
    public function __construct(
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        Skill $skill,
        string $targetUrl,
    ): ListItemView {
        return new ListItemView(
            id: sprintf('skill-item-%s', $skill->getId()),
            labels: [
                    new LabelView($skill->getContent()),
                ],
            indicators: $this->getIndicators($skill),
            status: $this->getStatus($skill),
            isDeleted: $skill->isDeleted(),
            dropdown: $this->dropDown($skill, $targetUrl),
            gridTemplateContent: 'grid-cols-[1fr_80px] lg:grid-cols-[1fr_150px]',
            gridTemplateBadges: 'grid-cols-1 lg:grid-cols-[1fr_2fr] gap-2 justify-items-center',
        );
        ;
    }

    private function dropDown(Skill $entity, string $targetUrl): DropdownView
    {
        if ($entity->isDeleted()) {
            return new DropdownView(
                menuItems: [
                    new LinkView(
                        label: 'Restaurer',
                        url: $this->urlContextService->generateUrl('admin_skill_restore', [
                            'skill' => $entity->getId()
                            ], $targetUrl),
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
                    url: $this->urlContextService->generateUrl('admin_skill_edit', ['skill' => $entity->getId()], $targetUrl),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                        new HtmlAttributeView('data-action', 'click->dropdown#close'),
                    ],
                ),
                 new LinkView(
                     label: 'Supprimer',
                     url: $this->urlContextService->generateUrl('admin_skill_delete', ['skill' => $entity->getId()], $targetUrl),
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

    private function getIndicators(Skill $entity): array
    {
        return [
            new BadgeView(
                value: $entity->getCategory()->getIcon(),
                variant: ColorVariant::ACCENT,
                size: Size::ICON
            )
        ];
    }

    private function getStatus(Skill $entity): ?BadgeView
    {
        if ($entity->isDeleted()) {
            return new BadgeView('Supprimée', ColorVariant::DESTRUCTIVE);
        }

        return null;
    }
}
