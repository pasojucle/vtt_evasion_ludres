<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\Health;
use App\Entity\Licence;
use DateInterval;
use DateTime;
use DateTimeInterface;

class HealthViewModel extends AbstractViewModel
{
    public ?Health $entity;

    public ?string $medicalCertificateDate;

    public ?bool $isNegativeHealthResponses;

    public ?bool $isMedicalCertificateRequired;

    public ?string $content = null;

    public DateTime|DateTimeInterface|null $medicalCertificateDateObject;

    private ServicesPresenter $services;

    public static function fromHealth(?Health $health, ServicesPresenter $services)
    {
        $healthView = new self();
        if ($health) {
            $medicalCertificateDate = $health->getMedicalCertificateDate();
            $healthView->entity = $health;
            $healthView->services = $services;
            $healthView->medicalCertificateDate = ($medicalCertificateDate) ? $medicalCertificateDate->format('d/m/Y') : null;
            $healthView->medicalCertificateDateObject = $medicalCertificateDate;
            $healthView->content = $health->getContent();
        }

        return $healthView;
    }

    public function isMedicalCertificateRequired(): string
    {
        $message = '';
        $medicalCertificateEndAt = null;

        if ($this->medicalCertificateDate) {
            $medicalCertificateEndAt = $this->getMedicalCertificateEndAt();
            $message .= 'Date du dernier certificat médical : ' . $this->medicalCertificateDate
                    . sprintf(' (Valable jusqu\'au %s) <br>', $medicalCertificateEndAt->format('d/m/Y'));
        }

        if ($this->entity->hasAtLeastOnePositveResponse()) {
            $message .= 'Vous avez répondu "oui" au moins à une réponse du questionnaire de santé. <br>';
        }

        if ($this->entity->hasAtLeastOnePositveResponse() || null === $medicalCertificateEndAt || $medicalCertificateEndAt < new DateTime()) {
            $message .= 'Vous devez joindre un certificat médical daté DE MOINS DE 12 MOIS de non contre-indication à la pratique du VTT';
        }

        return $message;
    }

    private function getMedicalCertificateEndAt(): DateTime
    {
        $duration = (Licence::TYPE_SPORT === $this->entity->getUser()->getLastLicence()->getType())
            ? $this->services->sportMedicalCertificateDuration
            : $this->services->hikeMedicalCertificateDuration;
        /** @var DateTime $medicalCertificateAt */
        $medicalCertificateAt = clone $this->medicalCertificateDateObject;
        $endAt = $medicalCertificateAt->add(new DateInterval(sprintf('P%sY', $duration)));

        return $endAt;
    }
}
