<?php

declare(strict_types=1);

namespace App\UseCase\v2\Licence;

use App\Entity\Licence;
use App\Repository\Interface\LicenceAgreementRepositoryInterface;
use App\Repository\Interface\LicenceRepositoryInterface;

class DeleteLicence
{
    public function __construct(
        private LicenceRepositoryInterface $licenceRepository,
        private LicenceAgreementRepositoryInterface $licenceAgreementRepository,
    ) {
    }

    public function __invoke(Licence $licence): void
    {
        foreach ($licence->getLicenceAgreements() as $licenceAgreement) {
            $this->licenceAgreementRepository->remove($licenceAgreement, false);
        }
        $this->licenceRepository->remove($licence);
    }
}
