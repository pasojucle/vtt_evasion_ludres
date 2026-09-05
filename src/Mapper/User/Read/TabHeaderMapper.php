<?php

declare(strict_types=1);

namespace App\Mapper\User\Read;

use App\Dto\Enum\ColorVariant;
use App\Dto\View\BadgeView;
use App\Dto\View\User\Tab\WrapperHeaderView;
use App\Entity\User;
use App\Mapper\Level\LevelBadgeMapper;
use App\Mapper\LicenceAuthorization\LicenceAuthorizationBadgeMapper;
use Symfony\Contracts\Translation\TranslatorInterface;

class TabHeaderMapper
{
    public function __construct(
        private TranslatorInterface $translator,
        private LicenceAuthorizationBadgeMapper $licenceAuthorizationBadgeMapper,
        private LevelBadgeMapper $levelBadgeMapper,
    ) {
    }
    public function mapToView(User $entity): WrapperHeaderView
    {
        $identity = $entity->getIdentity();
        $licence = $entity->getLastLicence();
        $season = $licence->getSeason();
        $level = $entity->getLevel();
        $authorizations = $licence->getLicenceAuthorizations();

        return new WrapperHeaderView(
            id: $entity->getId(),
            fullName: $identity->getFullName(),
            levelType: $level?->getType()->trans($this->translator),
            level: $this->levelBadgeMapper->mapToView($level),
            season: new BadgeView(
                value: sprintf('%s-%s', $season, $season + 1),
                variant: ColorVariant::ACCENT
            ),
            authorizations: array_map(fn ($authorization) => $this->licenceAuthorizationBadgeMapper->mapToview($authorization), $authorizations),
        );
    }
}
