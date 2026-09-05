<?php

declare(strict_types=1);

namespace App\Mapper\User\Read;


use App\Dto\View\User\Tab\IdentityView;
use App\Entity\Member;
use App\Entity\User;
use App\Mapper\Gardian\GardianReadMapper;
use App\Mapper\Identity\PassportPhotoMapper;
use Doctrine\Common\Collections\ArrayCollection;

class IdentityMapper
{
    public function __construct(
        private PassportPhotoMapper $passportPhotoMapper,
        private GardianReadMapper $gardianReadMapper,
    ) {
    }
    public function mapToView(User $entity): IdentityView
    {
        $identity = $entity->getIdentity();

        [$phones, $emergencyContact] = ($entity instanceof Member)
            ? [
                $entity->getMemberGardians()->map(fn ($gardian) => $this->gardianReadMapper->mapToView($gardian)),
                $entity->getEmergencyContact()?->getId(),
            ] : [new ArrayCollection(), null, null];

        return new IdentityView(
            id: $entity->getId(),
            identityId: $identity->getId(),
            passportPhoto: $this->passportPhotoMapper->mapToView($identity->getFilename()),
            fullName: $identity->getFullName(),
            gardians: $phones,
            emergencyContactId: $emergencyContact,
        );
    }
}
