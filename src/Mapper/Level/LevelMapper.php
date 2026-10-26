<?php

declare(strict_types=1);

namespace App\Mapper\Level;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemView;
use App\Entity\Level;
use App\Repository\MemberRepository;
use App\Service\CsrfTokenService;
use App\Service\UrlContextService;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class LevelMapper
{
    public function __construct(
        private MemberRepository $memberRepository,
        private UrlContextService $urlContextService,
        private CsrfTokenService $csrfTokenService,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function mapToView(
        Level $level,
        string $targetUrl,
        int $currentSeason,
    ): ListItemView {
        $tokenId = $this->csrfTokenService->getTokenId($level);
        $tokenValue = $this->csrfTokenManager->getToken($tokenId)->getValue();

        return new ListItemView(
            id: sprintf('level-item-%s', $level->getId()),
            labels: [
                new LabelView($level->getTitle()),
            ],
            sortableId: $level->getId(),
            indicators: $this->getIndicators($level),
            status: $this->getStatus($level),
            counter: $this->counter($level, $currentSeason),
            isDeleted: $level->isDeleted(),
            dropdown: $this->dropDown($level, $targetUrl),
            csrfToken: $tokenValue,
            gridTemplateRow: 'grid-cols-[20px_1fr]',
            gridTemplateBadges: 'grid-cols-[1fr_70px]',
        );
    }

    private function getIndicators(Level $level): array
    {
        return [
            new BadgeView(
                value: 'lucide:palette',
                size: Size::ICON,
                color: $level->getColor(),
            ),
            new BadgeView(
                value: $level->getType()->getIcon(),
                size: Size::ICON,
                variant: ColorVariant::ACCENT,
            ),
        ];
    }

    private function counter(Level $level, int $currentSeason): ?BadgeView
    {
        if ($level->isDeleted()) {
            return null;
        }
        
        return new BadgeView(
            value: (string) $this->memberRepository->countByLevelAndSeason($level, $currentSeason),
        );
    }

    private function dropDown(Level $level, ?string $targetUrl): DropdownView
    {
        if ($level->isDeleted()) {
            return new DropdownView(
                menuItems: [
                    new LinkView(
                        label: 'Restaurer',
                        url: $this->urlContextService->generateUrl('admin_level_restore', [
                            'level' => $level->getId()
                            ], $targetUrl),
                        icon: 'lucide:archive-restore',
                        variant: ColorVariant::DROPDOWN,
                    ),
                ]
            );
        }

        $menusItems = [];
        $menusItems[] = new LinkView(
            label: 'Modifier',
            url: $this->urlContextService->generateUrl('admin_level_edit', ['level' => $level->getId()], $targetUrl),
            icon: 'lucide:pencil',
            variant: ColorVariant::DROPDOWN,
            htmlAttributes: [
                new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                new HtmlAttributeView('data-action', 'click->dropdown#close'),
            ],
        );
        if (!$level->isProtected()) {
            $menusItems[] = new LinkView(
                label: 'Supprimer',
                url: $this->urlContextService->generateUrl('admin_level_delete', ['level' => $level->getId()], $targetUrl),
                icon: 'lucide:delete',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::MODAL_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close'),
                ],
            );
        }
        return  new DropdownView(
            menuItems: $menusItems
        );
    }

    private function getStatus(Level $level): ?BadgeView
    {
        if ($level->isDeleted()) {
            return new BadgeView('Supprimée', ColorVariant::DESTRUCTIVE);
        }

        return null;
    }
}
