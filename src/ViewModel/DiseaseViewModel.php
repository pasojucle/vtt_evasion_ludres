<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\Disease;
use App\Entity\DiseaseKind;

class DiseaseViewModel extends AbstractViewModel
{
    public ?string $label;

    public ?string $title;

    public ?string $category;

    public ?string $curentTreatment;

    public ?string $emergencyTreatment;

    public ?Disease $entity;

    public ?bool $hasTreatment;

    public ?bool $hasEmergencyTreatment;

    public static function fromDisease(Disease $disease)
    {
        $diseaseView = new self();
        $diseaseView->entity = $disease;
        $diseaseView->label = $disease->getDiseaseKind()->getName();
        $diseaseView->title = $disease->getTitle();
        $diseaseView->category = DiseaseKind::CATEGORIES[$disease->getDiseaseKind()->getCategory()];
        $diseaseView->curentTreatment = $disease->getCurentTreatment();
        $diseaseView->emergencyTreatment = $disease->getEmergencyTreatment();
        $diseaseView->hasTreatment = $disease->getDiseaseKind()->getCategory() === DiseaseKind::CATEGORY_DISEASE;
        $diseaseView->hasEmergencyTreatment = $disease->getDiseaseKind()->hasEmergencyTreatment();

        return $diseaseView;
    }
}
