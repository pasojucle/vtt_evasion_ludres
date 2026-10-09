<?php

declare(strict_types=1);

namespace App\Mapper\MemberStatus;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Dto\View\MemberStatus\MemberStatusView;
use App\Entity\Enum\PermissionEnum;
use App\Entity\Member;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MemberStatusReadMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private RoleHierarchyInterface $roleHierarchy,
    ) {
    }

    public function mapToView(Member $entity): MemberStatusView
    {
        $permissions = $entity->getPermissions();
        $reachableRoles = $this->roleHierarchy->getReachableRoleNames($entity->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $reachableRoles, true);

        return new MemberStatusView(
            id: $entity->getId(),
            boardRole: $entity->getBoardRole()?->getName() ?? 'Adhérent',
            permissions: !empty($permissions)
                ? array_map(fn (PermissionEnum $permision) => new BadgeView(
                    $permision->trans($this->translator),
                ), $permissions)
                : [new BadgeView(
                    $isAdmin ? 'Administrateur' : 'Aucun',
                )],
            action: new LinkView(
                url: $this->urlGenerator->generate('admin_member_status_edit', ['member' => $entity->getId()]),
                variant: ColorVariant::GOST,
                icon: 'lucide:pencil',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                ],
            )
        );
    }
}
