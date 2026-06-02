<?php

declare(strict_types=1);

namespace App\Mapper\Registration;

use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\HtmlAttributDto;
use App\Entity\User;
use App\Mapper\User\UserDropdownMapper;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationDropdownMapper
{
    public function __construct(
        private Security $security,
        private UrlGeneratorInterface $urlGenerator,
        private UserDropdownMapper $userDropdownMapper,
    ) {
    }
    public function mapToView(User $user): DropdownDto
    {
        $licence = $user->getLastLicence();
        $menuItems = $this->userDropdownMapper->getMenuItemsfromUser($user);
        if ($licence->getState()->toValidate()) {
            $menuItems[] = new ButtonDto(
                label: 'Inscription incompète',
                url: $this->urlGenerator->generate('admin_registration_reject', ['licence' => $licence->getId()]),
                icon: 'lucide:message-circle-warning',
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                ],
            );
            $menuItems[] = new ButtonDto(
                label: 'Supprimer l\'inscription',
                url: $this->urlGenerator->generate('admin_delete_licence', ['licence' => $licence->getId()]),
                icon: 'lucide:delete',
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                ],
            );
        }
        
        return new DropdownDto(
            title: $user->getIdentity()->getFullName(),
            menuItems: $menuItems,
        );
    }
}
