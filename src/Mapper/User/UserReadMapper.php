<?php

declare(strict_types=1);

namespace App\Mapper\User;

use App\Dto\Enum\ColorVariant;
use App\Dto\View\BadgeView;
use App\Dto\View\User\UserReadView;
use App\Entity\Member;
use App\Entity\User;
use App\Mapper\Gardian\GardianReadMapper;
use App\Mapper\Identity\PassportPhotoMapper;
use App\Mapper\LicenceAuthorization\LicenceAuthorizationBadgeMapper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserReadMapper
{
    public function __construct(
        private TranslatorInterface $translator,
        private LicenceAuthorizationBadgeMapper $LicenceAuthorizationBadgeMapper,
        private PassportPhotoMapper $passportPhotoMapper,
        private GardianReadMapper $gardianReadMapper,
    ) {
    }
    public function mapToView(User $entity): UserReadView
    {
        $identity = $entity->getIdentity();
        $licence = $entity->getLastLicence();
        $season = $licence->getSeason();
        $level = $entity->getLevel();
        $authorizations = $licence->getLicenceAuthorizations();

        [$phones, $emergencyContact] = ($entity instanceof Member)
            ? [
                $entity->getMemberGardians()->map(fn ($gardian) => $this->gardianReadMapper->mapToView($gardian)),
                $entity->getEmergencyContact()?->getId(),
            ]: [new ArrayCollection(), null];

        return new UserReadView(
            id: $entity->getId(),
            identityId: $identity->getId(),
            licenceId: $licence->getId(),
            passportPhoto: $this->passportPhotoMapper->mapToView($identity->getFilename()),
            fullName: $identity->getFullName(),
            levelType: $level->getType()->trans($this->translator),
            level: new BadgeView(
                value: $level->getTitle(),
                color: $level->getColor(),
            ),
            season: new BadgeView(
                value: sprintf('%s-%s', $season, $season + 1),
                variant: ColorVariant::ACCENT
            ),
            authorizations: array_map(fn($authorization) => $this->LicenceAuthorizationBadgeMapper->mapToview($authorization), $authorizations),
            gardians: $phones,
            emergencyContactId: $emergencyContact,
        );
    }
}
