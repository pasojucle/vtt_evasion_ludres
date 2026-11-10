<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\LicenceAgreement;

interface LicenceAgreementRepositoryInterface
{
    public function save(LicenceAgreement $licenceAgreement, bool $flush = true): void;

    public function remove(LicenceAgreement $licenceAgreement, bool $flush = true): void;
}
