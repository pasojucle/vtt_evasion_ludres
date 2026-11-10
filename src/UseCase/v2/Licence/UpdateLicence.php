<?php

declare(strict_types=1);

namespace App\UseCase\v2\Licence;

use App\Entity\Licence;
use App\Repository\Interface\LicenceRepositoryInterface;

class UpdateLicence
{
    public function __construct(
        private LicenceRepositoryInterface $licenceRepository,
    ) {
    }

    public function __invoke(Licence $licence): void
    {
        $this->licenceRepository->save($licence);
    }
}
