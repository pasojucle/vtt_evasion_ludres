<?php

declare(strict_types=1);

namespace App\UseCase\Survey;

use App\Entity\History;
use App\Entity\Survey;
use App\Form\Admin\SurveyType;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class SetSurvey
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private NotificationService $notificationService,
    ) {
    }

    public function execute(FormInterface $form, bool $persit = false): void
    {
        $survey = $form->getData();

        if (SurveyType::DISPLAY_BIKE_RIDE !== $survey->getRestriction()) {
            $survey->setBikeRide(null);
        }
        if (SurveyType::DISPLAY_MEMBER_LIST !== $survey->getRestriction()) {
            $survey->removeMembers();
        }

        if ($persit) {
            $this->entityManager->persist($survey);
        }
        
        $this->entityManager->flush();

        $this->notify($survey);
    }

    private function notify(Survey $survey): void
    {
        $histories = $this->entityManager->getRepository(History::class)->findNotifiableBySurvey($survey->getId());

        if ($histories) {
            $this->notificationService->notify($survey);
        }
    }
}
