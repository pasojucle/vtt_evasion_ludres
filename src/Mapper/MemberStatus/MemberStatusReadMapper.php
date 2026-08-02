<?php

declare(strict_types=1);

namespace App\Mapper\MemberStatus;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\MemberStatus\MemberStatusView;
use App\Dto\View\LinkView;
use App\Entity\Enum\PermissionEnum;
use App\Entity\Member;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MemberStatusReadMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
    ){}

    public function mapToView(Member $entity): MemberStatusView
    {
        $level = $entity->getLevel();
        $permissions = $entity->getPermissions();

        return new MemberStatusView(
            id: $entity->getId(),
            level: new BadgeView(
                value: $level->getTitle(),
                color: $level->getColor(),
            ),
            levelType: $level->getType()->trans($this->translator),
            boardRole: $entity->getBoardRole()?->getName() ?? 'Adhérent',
            permissions: empty($permissions)
                ? [new BadgeView(
                    'Aucun',
                )]
                : array_map(fn(PermissionEnum $permision) => new BadgeView(
                    $permision->trans($this->translator),
                ), $permissions),
            action: new LinkView(
                url: $this->urlGenerator->generate('admin_member_status_edit', ['member' => $entity->getId()]),
                variant: ColorVariant::GOST,
                icon: 'lucide:pencil',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                ],
            )
        );
    }
}