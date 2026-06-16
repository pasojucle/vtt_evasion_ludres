<?php

declare(strict_types=1);

namespace App\Dto\Form;

use App\Entity\Licence;
use DateTimeInterface;

class LicenceRegister
{
    public function __construct(
        public Licence $licence,
        public string $licenceNumber,
        public ?DateTimeInterface $medicalCertificateDate = null,
    ) {
    }
}
