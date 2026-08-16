<?php

declare(strict_types=1);

namespace App\Mapper\MemberLevel;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LinkView;
use App\Dto\View\MemberLevel\MemberLevelView;
use App\Entity\Member;
use App\Mapper\Level\LevelBadgeMapper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MemberLevelReadMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private LevelBadgeMapper $levelBadgeMapper,
    ) {
    }

    public function mapToView(Member $entity): MemberLevelView
    {
        $level = $entity->getLevel();

        return new MemberLevelView(
            id: $entity->getId(),
            level: $this->levelBadgeMapper->mapToView($level),
            levelType: $level?->getType()->trans($this->translator),
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
