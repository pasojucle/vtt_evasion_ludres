<?php

declare(strict_types=1);

namespace App\Mapper\User;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\LabelView;
use App\Dto\View\ListItemUrlView;
use App\Dto\View\ListItemView;
use App\Entity\Member;
use App\Entity\User;
use App\Mapper\Level\LevelBadgeMapper;
use App\Service\UrlContextService;

class UserMapper
{
    public function __construct(
        private UserDropdownMapper $userDropdownMapper,
        private LevelBadgeMapper $levelBadgeMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        User $entity,
        string $targetUrl,
    ): ListItemView {
        $identity = $entity->getIdentity();
        $level = $entity->getLevel();
        return new ListItemView(
            id: sprintf('user-item-%s', $entity->getId()),
            labels: [
                new LabelView($identity->getFullName()),
            ],
            indicators: $this->getIndicators($entity),
            status: $this->levelBadgeMapper->mapToView($level),
            dropdown: $this->userDropdownMapper->mapToView($entity, $targetUrl),
            url: new ListItemUrlView($this->urlContextService->generateUrl("admin_user_show", ['user' => $entity->getId()], $targetUrl)),
            gridTemplateContent: 'grid-cols-1 lg:grid-cols-[2fr_1fr]',
            gridTemplateBadges: 'grid-cols-[auto_160px]',
        );
    }

    private function getIndicators(User $entity): array
    {
        $indicators = [
            ($entity->getLevel())
            ? new BadgeView(
                value: $entity->getLevel()->getType()->getIcon(),
                variant: ColorVariant::ACCENT,
                size: Size::ICON,
            )
            : new BadgeView(
                value: 'lucide:badge-help',
                variant: ColorVariant::WARNING,
                size: Size::ICON,
            ),
            new BadgeView(
                value: (string) $entity->getLastLicence()->getSeason(),
                variant: ColorVariant::AMBER,
            ),
        ];

        $boardRole = ($entity instanceof Member) ? $entity->getBoardRole() : null;

        if ($boardRole?->isBoard()) {
            array_unshift(
                $indicators,
                new BadgeView(
                    value: 'lucide:crown',
                    variant: ColorVariant::ACCENT,
                    size: Size::ICON
                )
            );
        }

        return $indicators;
    }
}
