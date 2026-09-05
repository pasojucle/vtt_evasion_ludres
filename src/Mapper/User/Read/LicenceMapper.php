<?php

declare(strict_types=1);

namespace App\Mapper\User\Read;


use App\Dto\View\User\Tab\LicenceView;
use App\Entity\Member;
use App\Entity\User;

class LicenceMapper
{
    public function mapToView(User $entity): LicenceView
    {
        $licence = $entity->getLastLicence();

        return new LicenceView(
            id: $entity->getId(),
            licenceId: $licence->getId(),
            healthId: ($entity instanceof Member)
                ? $entity->getHealth()->getId()
                : null,
        );
    }
}
