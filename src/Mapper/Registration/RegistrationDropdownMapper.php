<?php

declare(strict_types=1);

namespace App\Mapper\Registration;

use App\Dto\Enum\ColorVariant;
use App\Dto\View\ButtonView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Entity\User;
use App\Mapper\User\UserDropdownMapper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationDropdownMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UserDropdownMapper $userDropdownMapper,
    ) {
    }
    public function mapToView(User $user, string $referer): DropdownView
    {
        $licence = $user->getLastLicence();
        $menuItems = $this->userDropdownMapper->getMenuItemsfromUser($user, $referer);
        if ($licence->getState()->toValidate()) {
            $menuItems[] = new ButtonView(
                label: 'Inscription incompète',
                url: $this->urlGenerator->generate('admin_registration_reject', ['licence' => $licence->getId()]),
                icon: 'lucide:message-circle-warning',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::MODAL_CONTENT),
                ],
            );
            $menuItems[] = new ButtonView(
                label: 'Supprimer l\'inscription',
                url: $this->urlGenerator->generate('admin_delete_licence', ['licence' => $licence->getId()]),
                icon: 'lucide:delete',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::MODAL_CONTENT),
                ],
            );
        }
        
        return new DropdownView(
            title: $user->getIdentity()->getFullName(),
            menuItems: $menuItems,
        );
    }
}
