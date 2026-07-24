<?php

declare(strict_types=1);

namespace App\Mapper\User;

use App\Dto\Enum\ColorVariant;
use App\Dto\View\BadgeView;
use App\Dto\View\User\UserReadDto;
use App\Entity\User;
use App\Mapper\Identity\PassportPhotoMapper;
use App\Mapper\Licence\LicenceAgreementMapper;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserReadMapper
{
    public function __construct(
        private TranslatorInterface $translator,
        private LicenceAgreementMapper $licenceAgreementMapper,
        private PassportPhotoMapper $passportPhotoMapper,
    ) {
    }
    public function mapToView(User $entity): UserReadDto
    {
        $identity = $entity->getIdentity();
        $licence = $entity->getLastLicence();
        $season = $licence->getSeason();
        $level = $entity->getLevel();
        $authorizations = $licence->getLicenceAuthorizations();

        return new UserReadDto(
            id: $identity->getId(),
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
            authorizations: array_map(fn($authorization) => $this->licenceAgreementMapper->mapToview($authorization), $authorizations),
        );
    }
}
