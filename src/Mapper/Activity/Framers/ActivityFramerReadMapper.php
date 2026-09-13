<?php

declare(strict_types=1);

namespace App\Mapper\Activity\Framers;

use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\ListDrawerItemView;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Member;
use App\Mapper\Level\LevelBadgeMapper;
use App\Service\UrlContextService;

class ActivityFramerReadMapper
{
    public function __construct(
        private LevelBadgeMapper $levelBadgeMapper,
        private UrlContextService $urlContextService,
    ) {
    }


    public function mapToView(
        Member $framer,
        ?AvailabilityEnum $availability,
        string $fallback
    ): ListDrawerItemView {
        $identity = $framer->getIdentity();
        $availability = $availability ?? AvailabilityEnum::NONE;

        return new ListDrawerItemView(
            label: $identity->getFullName(),
            indicators: [$this->levelBadgeMapper->mapToView($framer->getLevel())],
            status: new BadgeView(
                value: $availability->getIcon(),
                variant: $availability->variant(),
                size: Size::ICON
            ),
            url: $this->urlContextService->generateUrl(
                'admin_user_show',
                ['user' => $framer->getId()],
                $fallback,
            ),
        );
    }
}
