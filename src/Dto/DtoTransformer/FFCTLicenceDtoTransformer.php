<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\FFCTLicenceDto;
use App\Dto\IdentityDto;
use App\Dto\UserDto;
use App\Entity\Licence;

class FFCTLicenceDtoTransformer
{
    public function fromEntity(UserDto $user): FFCTLicenceDto
    {
        $subscriber = (Licence::CATEGORY_MINOR === $user->lastLicence->category)
                ? $user->kinship
                : $user->member;
        $children = ($user->member && $user->kinship) ? $user->member : null;

        $FFCTLicenceDto = new FFCTLicenceDto();
        $FFCTLicenceDto->fullName = $this->getFullName($subscriber);
        $FFCTLicenceDto->fullNameChildren = $this->getFullName($children);
        $FFCTLicenceDto->birthDate = $this->getBirthDate($subscriber);
        $FFCTLicenceDto->birthDateChildren = $this->getBirthDate($children);

        return $FFCTLicenceDto;
    }


    public function getFullName(?IdentityDto $identity): string
    {
        if (null !== $identity) {
            return sprintf('%s %s', $identity->name, $identity->firstName);
        }

        return '';
    }

    public function getBirthDate(?IdentityDto $identity): ?string
    {
        if (null !== $identity?->birthDate) {
            return $identity->birthDate;
        }

        return '';
    }
}
