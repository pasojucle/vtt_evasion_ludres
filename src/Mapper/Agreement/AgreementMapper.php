<?php

declare(strict_types=1);

namespace App\Mapper\Agreement;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemView;
use App\Dto\View\ToggleStatusView;
use App\Entity\Agreement;
use App\Entity\Enum\AgreementKindEnum;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\LicenceMembershipEnum;
use App\Service\CsrfTokenService;
use App\Service\UrlContextService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgreementMapper
{
    public function __construct(
        private UrlContextService $urlContextService,
        private UrlGeneratorInterface $urlGenerator,
        private CsrfTokenService $csrfTokenService,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private TranslatorInterface $translator,
    ) {
    }

    public function mapToView(
        Agreement $agreement,
        string $targetUrl,
    ): ListItemView {
        $tokenId = $this->csrfTokenService->getTokenId($agreement);
        $tokenValue = $this->csrfTokenManager->getToken($tokenId)->getValue();

        return new ListItemView(
            id: sprintf('agreement-item-%s', $agreement->getId()),
            labels: [
                new LabelView($agreement->getTitle()),
            ],
            indicators: $this->indicators($agreement),
            status: $this->getStatus($agreement, $tokenId),
            dropdown: $this->dropDown($agreement, $targetUrl),
            action: $this->action($agreement, $tokenValue),
            csrfToken: $tokenValue,
            gridTemplateRow: 'grid-cols-[20px_1fr_50px]',
            gridTemplateLabels: 'grid-cols-2 lg:grid-cols-1',
            gridTemplateBadges: 'grid-cols-[1fr_70px]',
        );
    }
    private function dropDown(Agreement $agreement, string $targetUrl): DropdownView
    {
        return  new DropdownView(
            menuItems: [
                new LinkView(
                    label: 'Modifier',
                    url: $this->urlContextService->generateUrl('admin_agreement_edit', ['agreement' => $agreement->getId()], $targetUrl),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                        new HtmlAttributeView('data-action', 'click->dropdown#close'),
                    ],
                ),
            ]
        );
    }

    private function getStatus(Agreement $agreement, string $tokenId): BadgeView
    {
        $status = $agreement->getStatus();
        return new BadgeView(
            value: $status->trans($this->translator),
            variant: $status->variant(),
            toggleStatusId:$tokenId,
        );
    }


    private function indicators(Agreement $agreement): array
    {
        $indicators = [
            new BadgeView(
                value: $agreement->getKind()->icon(),
                variant: ColorVariant::DEFAULT,
                size: Size::ICON
            )
        ];

        $categories = [LicenceCategoryEnum::SCHOOL, LicenceCategoryEnum::ADULT];
        $currentCategory = $agreement->getCategory();
        foreach ($categories as $category) {
            if ($category !== $currentCategory) {
                $indicators[] = new BadgeView(
                    value: $category->icon(),
                    variant: ColorVariant::ACCENT,
                    size: Size::ICON
                );
            }
        }

        $memberShips = [LicenceMembershipEnum::TRIAL, LicenceMembershipEnum::YEARLY];
        $currentMemberShip = $agreement->getMembership();
        foreach ($memberShips as $memberShip) {
            if ($memberShip !== $currentMemberShip) {
                $indicators[] = new BadgeView(
                    value: $memberShip->icon(),
                    variant: ColorVariant::ACCENT,
                    size: Size::ICON
                );
            }
        }

        return $indicators;
    }

    private function action(Agreement $agreeement, string $csrfToken): ToggleStatusView
    {
        return new ToggleStatusView(
            url: $this->urlGenerator->generate('admin_agreement_toggle', ['agreement' => $agreeement->getId()]),
            csrfToken: $csrfToken,
            isActive: !$agreeement->isDisabled(),
        );
    }
}
