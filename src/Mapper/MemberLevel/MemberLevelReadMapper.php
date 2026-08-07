<?php

declare(strict_types=1);

namespace App\Mapper\MemberLevel;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\MemberLevel\MemberLevelView;
use App\Dto\View\LinkView;
use App\Entity\Member;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MemberLevelReadMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
    ){}

    public function mapToView(Member $entity): MemberLevelView
    {
        $level = $entity->getLevel();

        return new MemberLevelView(
            id: $entity->getId(),
            level: new BadgeView(
                value: $level->getTitle(),
                color: $level->getColor(),
            ),
            levelType: $level->getType()->trans($this->translator),
            action: new LinkView(
                url: $this->urlGenerator->generate('admin_member_level_edit', ['member' => $entity->getId()]),
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