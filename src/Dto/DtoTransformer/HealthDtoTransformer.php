<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\HealthDto;
use App\Dto\LicenceDto;
use App\Entity\Health;
use DateInterval;
use DateTime;
use DateTimeImmutable;

class HealthDtoTransformer
{
    public function fromEntity(?Health $health, LicenceDto $lastLicence): HealthDto
    {
        $healthDto = new HealthDto();
        if ($health) {
            $medicalCertificateDate = $health->getMedicalCertificateDate();
            $healthDto->medicalCertificateDate = ($medicalCertificateDate) ? $medicalCertificateDate->format('d/m/Y') : null;
            $healthDto->content = $health->getContent();
            $healthDto->isMedicalCertificateRequired = $this->isMedicalCertificateRequired($medicalCertificateDate);
        }

        return $healthDto;
    }

    public function isMedicalCertificateRequired(null|DateTime|DateTimeImmutable $medicalCertificateDate): string
    {
        $message = '';

        if ($medicalCertificateDate) {
            $message .= 'Date du dernier certificat médical : ' . $medicalCertificateDate->format('d/m/Y');
        }

        return $message;
    }
}
