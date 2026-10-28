<?php

declare(strict_types=1);

namespace App\Mapper\BoardRole;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemView;
use App\Entity\BoardRole;
use App\Service\CsrfTokenService;
use App\Service\UrlContextService;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class BoardRoleMapper
{
    public function __construct(
        private UrlContextService $urlContextService,
        private CsrfTokenService $csrfTokenService,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function mapToView(
        BoardRole $boardRole,
        string $targetUrl,
    ): ListItemView {

        $tokenId = $this->csrfTokenService->getTokenId($boardRole);
        $tokenValue = $this->csrfTokenManager->getToken($tokenId)->getValue();


        return new ListItemView(
            id: sprintf('board-role-item-%s', $boardRole->getId()),
            labels: [
                new LabelView($boardRole->getName()),
            ],
            indicators: $this->indicators($boardRole),
            sortableId: $boardRole->getId(),
            status: $this->getStatus($boardRole),
            isDeleted: $boardRole->isDeleted(),
            dropdown: $this->dropDown($boardRole, $targetUrl),
            csrfToken: $tokenValue,
            gridTemplateRow: 'grid-cols-[20px_1fr]',
            gridTemplateContent: 'grid-cols-[1fr_80px]'
        );
        
    }
    private function dropDown(BoardRole $boardRole, string $targetUrl): DropdownView
    {
        if ($boardRole->isDeleted()) {
            return new DropdownView(
                menuItems: [
                    new LinkView(
                        label: 'Restaurer',
                        url: $this->urlContextService->generateUrl('admin_board_role_restore', [
                            'boardRole' => $boardRole->getId()
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
                    url: $this->urlContextService->generateUrl('admin_board_role_edit', ['boardRole' => $boardRole->getId()], $targetUrl),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                     htmlAttributes: [
                        new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                        new HtmlAttributeView('data-action', 'click->dropdown#close'),
                    ],
                ),
                 new LinkView(
                     label: 'Supprimer',
                     url: $this->urlContextService->generateUrl('admin_board_role_delete', ['boardRole' => $boardRole->getId()], $targetUrl),
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

    private function getStatus(BoardRole $boardRole): ?BadgeView
    {
        if ($boardRole->isDeleted()) {
            return new BadgeView('Supprimée', ColorVariant::DESTRUCTIVE);
        }

        return null;
    }


    private function indicators(BoardRole $boardRole): array
    {
        return ($boardRole->isBoard())
            ? [
                new BadgeView(
                    value: 'lucide:crown',
                    variant: ColorVariant::ACCENT,
                    size: Size::ICON
                ),
            ]
            : [];
    }
}
