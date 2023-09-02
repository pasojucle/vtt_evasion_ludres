<?php

declare(strict_types=1);

namespace App\UseCase\Survey;

use App\Form\Admin\SurveyType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class SetSurvey
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function execute(FormInterface $form): void
    {
        $survey = $form->getData();

        if (SurveyType::DISPLAY_BIKE_RIDE !== $survey->getRestriction()) {
            $survey->setBikeRide(null);
        }
        if (SurveyType::DISPLAY_MEMBER_LIST !== $survey->getRestriction()) {
            $survey->removeMembers();
        }

        $this->entityManager->persist($survey);
        $this->entityManager->flush();
    }
}
