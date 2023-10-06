<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\HealthDto;
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

    public function fromEntity(?Health $health): HealthDto
    {
        $healthDto = new HealthDto();
        if ($health) {
            $medicalCertificateDate = $health->getMedicalCertificateDate();
            $healthDto->medicalCertificateDate = ($medicalCertificateDate) ? $medicalCertificateDate->format('d/m/Y') : null;
            $healthDto->content = $health->getContent();
            $healthDto->isMedicalCertificateRequired = $this->isMedicalCertificateRequired($health, $medicalCertificateDate);
        }

        return $healthDto;
    }

    public function isMedicalCertificateRequired(Health $health, null|DateTime|DateTimeImmutable $medicalCertificateDate): string
    {
        $message = '';
        $medicalCertificateEndAt = null;

        if ($medicalCertificateDate) {
            $medicalCertificateEndAt = $this->getMedicalCertificateEndAt($health->getUser()->getLastLicence()->getType(), clone $medicalCertificateDate);
            $message .= 'Date du dernier certificat médical : ' . $medicalCertificateDate->format('d/m/Y')
                    . sprintf(' (Valable jusqu\'au %s) <br>', $medicalCertificateEndAt->format('d/m/Y'));
        }

        if ($health->hasAtLeastOnePositveResponse()) {
            $message .= 'Vous avez répondu "oui" au moins à une réponse du questionnaire de santé. <br>';
        }
        
        if (!$health->hasAtLeastOnePositveResponse() && new DateTime() <= $medicalCertificateEndAt ) {
            $message .= 'J\'atteste avoir répondu "NON" à toutes les questions du questionnaire de santé et ne pas fournir de nouveau certificat médical pour ma réinscription. <br>';
        }

        if ($health->hasAtLeastOnePositveResponse() || null === $medicalCertificateEndAt || $medicalCertificateEndAt < new DateTime()) {
            $message .= 'Vous devez joindre un certificat médical daté DE MOINS DE 12 MOIS de non contre-indication à la pratique du VTT';
        }

        return $message;
    }

    private function getMedicalCertificateEndAt(int $licenceType, DateTime $medicalCertificateAt): DateTime
    {
        $duration = (Licence::TYPE_SPORT === $licenceType)
            ? $this->parameterService->getParameterByName('SPORT_MEDICAL_CERTIFICATE_DURATION')
            : $this->parameterService->getParameterByName('HIKE_MEDICAL_CERTIFICATE_DURATION');

        $endAt = $medicalCertificateAt->add(new DateInterval(sprintf('P%sY', $duration)));

        return $endAt;
    }
}
