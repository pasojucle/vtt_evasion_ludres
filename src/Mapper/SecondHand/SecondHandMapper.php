<?php

declare(strict_types=1);

namespace App\Mapper\SecondHand;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemUrlView;
use App\Dto\View\ListItemView;
use App\Entity\SecondHand;
use App\Service\UrlContextService;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecondHandMapper
{
    public function __construct(
        private TranslatorInterface $translator,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        SecondHand $entity,
        string $targetUrl,
    ): ListItemView {
        $state = $entity->getState();
        return new ListItemView(
            id: sprintf('secondhand-item-%s', $entity->getId()),
            labels: [
                new LabelView($entity->getName()),
            ],
            indicators: $this->getIndicators($entity),
            status: new BadgeView(
                $state->trans($this->translator),
                $state->variant(),
            ),
            dropdown: $this->dropDown($entity, $targetUrl),
            url: new ListItemUrlView(
                $this->urlContextService->generateUrl(
                    "admin_second_hand_show",
                    ['secondHand' => $entity->getId()],
                    $targetUrl
                )
            ),
            gridTemplateBadges: 'grid-cols-2',
        );
    }

    private function getIndicators(SecondHand $entity): array
    {
        return [
            new BadgeView(
                value: $entity->getCategory()->getIcon(),
                variant: ColorVariant::ACCENT,
                size: Size::ICON,
            )
        ];
    }

    private function dropDown(SecondHand $entity, string $targetUrl): DropdownView
    {
        return  new DropdownView(
            menuItems: [
                new LinkView(
                    label: 'Modifier',
                    url: $this->urlContextService->generateUrl('admin_second_hand_edit', ['secondHand' => $entity->getId()], $targetUrl),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                ),
                new LinkView(
                    label: 'Supprimer',
                    url: $this->urlContextService->generateUrl('admin_second_hand_delete', ['secondHand' => $entity->getId()], $targetUrl),
                    icon: 'lucide:delete',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributeView('data-turbo-frame', LinkView::MODAL_CONTENT),
                        new HtmlAttributeView('data-action', 'click->dropdown#close'),
                    ],
                ),
            ]
        );
    }
}
