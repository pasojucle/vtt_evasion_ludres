<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\Disease;

class DiseaseViewModel extends AbstractViewModel
{
    public ?string $label;

    public ?string $title;

    public ?int $type;

    public ?string $curentTreatment;

    public ?string $emergencyTreatment;

    public ?Disease $entity;

    public static function fromDisease(Disease $disease)
    {
        $diseaseView = new self();
        $diseaseView->entity = $disease;
        $diseaseView->label = $disease->getLabel();
        $diseaseView->title = $disease->getTitle();
        $diseaseView->type = $disease->getType();
        $diseaseView->curentTreatment = $disease->getCurentTreatment();
        $diseaseView->emergencyTreatment = $disease->getEmergencyTreatment();

        return $diseaseView;
    }
}
