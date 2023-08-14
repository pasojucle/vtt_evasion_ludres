<?php

declare(strict_types=1);

namespace App\Dto;

use DateTime;
use DateTimeInterface;

class HealthDto
{
    public ?string $medicalCertificateDate;

    public ?bool $isNegativeHealthResponses;

    public ?string $isMedicalCertificateRequired = '';

    public ?string $content = null;

    public DateTime|DateTimeInterface|null $medicalCertificateDateObject;
}
