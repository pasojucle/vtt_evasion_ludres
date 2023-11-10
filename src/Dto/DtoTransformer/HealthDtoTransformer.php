<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\HealthDto;
use App\Dto\LicenceDto;
use App\Entity\Health;
use App\Entity\Licence;
use App\Service\ParameterService;
use DateInterval;
use DateTime;
use DateTimeImmutable;

class HealthDtoTransformer
{
    public function __construct(
        private ParameterService $parameterService
    ) {
    }

    public function fromEntity(?Health $health, LicenceDto $lastLicence): HealthDto
    {
        $healthDto = new HealthDto();
        if ($health) {
            $medicalCertificateDate = $health->getMedicalCertificateDate();
            $healthDto->medicalCertificateDate = ($medicalCertificateDate) ? $medicalCertificateDate->format('d/m/Y') : null;
            $healthDto->content = $health->getContent();
            $healthDto->isMedicalCertificateRequired = $this->isMedicalCertificateRequired($health, $medicalCertificateDate, $lastLicence);
        }

        return $healthDto;
    }

    public function isMedicalCertificateRequired(Health $health, null|DateTime|DateTimeImmutable $medicalCertificateDate, LicenceDto $lastLicence): string
    {
        $message = '';
        $medicalCertificateEndAt = null;

        if ($medicalCertificateDate) {
            $medicalCertificateEndAt = $this->getMedicalCertificateEndAt(clone $medicalCertificateDate);
            $message .= 'Date du dernier certificat médical : ' . $medicalCertificateDate->format('d/m/Y')
                    . sprintf(' (Valable jusqu\'au %s) <br>', $medicalCertificateEndAt->format('d/m/Y'));
        }

        // if ($lastLicence->isFinal && null === $medicalCertificateEndAt || $medicalCertificateEndAt < new DateTime()) {
        //     $message .= 'Vous devez joindre un certificat médical daté DE MOINS DE 12 MOIS de non contre-indication à la pratique du VTT';
        // }

        return $message;
    }

    private function getMedicalCertificateEndAt(DateTime $medicalCertificateAt): DateTime
    {
        $duration = $this->parameterService->getParameterByName('HIKE_MEDICAL_CERTIFICATE_DURATION');

        $endAt = $medicalCertificateAt->add(new DateInterval(sprintf('P%sY', $duration)));

        return $endAt;
    }
}
