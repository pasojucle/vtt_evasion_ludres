<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Identity;

class IdentityService
{
    public function __construct(
        private ProjectDirService $projectDirService
    )
    {
    }

    public function getPicture(?string $picture): string
    {
        return (null !== $picture) ? $this->projectDirService->dir('', 'upload', $picture) : '/images/default-user-picture.jpg';
    }

    public function getBirthplace(Identity $identity): array
    {
        $birthCommune = $identity->getBirthCommune();
        if ($birthCommune) {
            return [
                $birthCommune->getName(),
                sprintf('%s - %s', $birthCommune->getDepartment()->getId(), $birthCommune->getDepartment()->getName()),
                'France'];
        }

        return [$identity->getBirthPlace(), null, $identity->getBirthCountry()];
    }
}