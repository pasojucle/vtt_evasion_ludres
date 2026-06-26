<?php

declare(strict_types=1);

namespace App\Mapper\User;

use App\Dto\Enum\ColorVariant;
use App\Dto\View\ButtonView;
use App\Dto\View\DropdownView;
use App\Entity\Enum\LevelType;
use App\Entity\User;
use App\Service\UrlContextService;
use Symfony\Bundle\SecurityBundle\Security;

class UserDropdownMapper
{
    public function __construct(
        private Security $security,
        private UrlContextService $urlContextService,
    ) {
    }
    public function mapToView(User $user, ?string $referer): DropdownView
    {
        return new DropdownView(
            title: $user->getIdentity()->getFullName(),
            menuItems: $this->getMenuItemsfromUser($user, $referer),
        );
    }

    public function getMenuItemsfromUser(User $user, string $referer): array
    {
        $menuItems = [];
        $level = $user->getLevel();
        if ($this->security->isGranted('USER_LIST') && $level?->getType() === LevelType::SCHOOL) {
            $menuItems[] = new ButtonView(
                label: 'Compétences',
                variant: ColorVariant::DROPDOWN,
                url: $this->urlContextService->generateUrl('admin_member_skill_edit', ['member' => $user->getId()], $referer),
                icon: 'lucide:graduation-cap',
            );
        }
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $menuItems[] = new ButtonView(
                label: 'Participation',
                variant: ColorVariant::DROPDOWN,
                url: $this->urlContextService->generateUrl('admin_user_participation', ['user' => $user->getId()], $referer),
                icon: 'lucide:chart-line',
            );
            $menuItems[] = new ButtonView(
                label: 'Attestation d\'inscription CE',
                variant: ColorVariant::DROPDOWN,
                url: $this->urlContextService->generateUrl('admin_user_certificate', ['member' => $user->getId()], $referer),
                icon: 'lucide:file-user',
            );
            if ($level?->isAccompanyingCertificat()) {
                $menuItems[] = new ButtonView(
                    label: 'Attestation adulte accompagnateur',
                    variant: ColorVariant::DROPDOWN,
                    url: $this->urlContextService->generateUrl('admin_user_accompanying_certificate', ['member' => $user->getId()], $referer),
                    icon: 'lucide:file-terminal',
                );
            }
            if ($this->security->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
                $menuItems[] = new ButtonView(
                    label: 'Se connecter en tant que',
                    variant: ColorVariant::DROPDOWN,
                    url: $this->urlContextService->generateUrl('home', ['_switch_user' => $user->getLicenceNumber()], $referer),
                    icon: 'lucide:arrow-left-right',
                );
            }
        }

        return $menuItems;
    }
}
