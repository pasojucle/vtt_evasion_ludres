<?php

declare(strict_types=1);

namespace App\Mapper\User;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Entity\User;
use App\Service\UrlContextService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserDropdownMapper
{
    public function __construct(
        private Security $security,
        private UrlContextService $urlContextService,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }
    public function mapToView(User $user, ?string $fallback): DropdownView
    {
        return new DropdownView(
            variant: DropdownVariant::BUTTON,
            menuItems: $this->getMenuItemsfromUser($user, $fallback),
            rounded: RoundedVariant::ROUNDED,
        );
    }

    public function getMenuItemsfromUser(User $user, string $fallback): array
    {
        $menuItems = [];
        $level = $user->getLevel();
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $menuItems[] = new LinkView(
                label: 'Attestation d\'inscription CE',
                variant: ColorVariant::DROPDOWN,
                url: $this->urlContextService->generateUrl('admin_user_certificate', ['member' => $user->getId()], $fallback),
                icon: 'lucide:file-user',
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close')
                ],
            );
            if ($level?->isAccompanyingCertificat()) {
                $menuItems[] = new LinkView(
                    label: 'Attestation adulte accompagnateur',
                    variant: ColorVariant::DROPDOWN,
                    url: $this->urlContextService->generateUrl('admin_user_accompanying_certificate', ['member' => $user->getId()], $fallback),
                    icon: 'lucide:file-terminal',
                    htmlAttributes: [
                        new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                        new HtmlAttributeView('data-action', 'click->dropdown#close')
                    ],
                );
            }
            if ($this->security->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
                $menuItems[] = new LinkView(
                    label: 'Se connecter en tant que',
                    variant: ColorVariant::DROPDOWN,
                    url: $this->urlGenerator->generate('home', ['_switch_user' => $user->getLicenceNumber()]),
                    icon: 'lucide:arrow-left-right',
                );
            }
        }

        return $menuItems;
    }
}
