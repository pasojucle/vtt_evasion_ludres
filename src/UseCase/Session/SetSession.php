<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use App\Entity\BikeRide;
use App\Entity\Respondent;
use App\Entity\Session;
use App\Entity\User;
use App\Service\CacheService;
use App\Service\LogService;
use App\Service\NotificationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\Form\FormInterface;

class SetSession
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ConfirmationSession $confirmationSession,
        private readonly NotificationService $notificationService,
        private readonly CacheService $cacheService,
        private readonly LogService $logService,
    ) {
    }

    public function add(FormInterface $form, User $user, BikeRide $bikeRide): void
    {
        $session = $form->get('session')->getData();
        $user->addSession($session);
        $responses = ($form->has('responses')) ? $form->get('responses')->getData() : null;
        if ($responses && !empty($surveyResponses = $responses['surveyResponses'])) {
            $this->addSurveyResponses($surveyResponses, $user, $bikeRide);
        }
        $this->confirmationSession->execute($session);
        $this->cacheService->deleteCacheIndex($session->getCluster());
        $this->entityManager->persist($session);
        $this->entityManager->flush();
        $this->writeLog($bikeRide, $user);

        $this->notificationService->notify($session);
    }

    public function edit(FormInterface $form, Session $session): void
    {
        $session = $form->get('session')->getData();
        $responses = ($form->has('responses')) ? $form->get('responses')->getData() : null;
        $bikeRide = $session->getCluster()->getBikeRide();
        $user = $session->getUser();
        if ($responses && !empty($surveyResponses = $responses['surveyResponses'])) {
            $unitOfWork = $this->entityManager->getUnitOfWork();
            $entityState = UnitOfWork::STATE_MANAGED;
            foreach ($surveyResponses as $response) {
                $responseEntityState = $unitOfWork->getEntityState($response);
                if (UnitOfWork::STATE_MANAGED < $responseEntityState) {
                    $entityState = $responseEntityState;
                    break;
                }
            }
            if (UnitOfWork::STATE_MANAGED < $entityState) {
                $this->addSurveyResponses($surveyResponses, $user, $bikeRide);
            }
        }
        $this->entityManager->flush();
        $this->cacheService->deleteCacheIndex($session->getCluster());
        $this->confirmationSession->execute($session);
        $this->writeLog($bikeRide, $user);
    }

    private function addSurveyResponses(array $surveyResponses, User $user, BikeRide $bikeRide): void
    {
        foreach ($surveyResponses as $response) {
            if (!$bikeRide->getSurvey()->isAnonymous()) {
                $response->setUser($user);
            }
            $this->entityManager->persist($response);
        }
        $now = new DateTime();

        $respondent = new Respondent();
        $respondent->setUser($user)
            ->setSurvey($bikeRide->getSurvey())
            ->setCreatedAt($now)
        ;
        $this->entityManager->persist($respondent);
    }

    private function writeLog(BikeRide $bikeRide, User $user): void
    {
        if ($survey = $bikeRide->getSurvey()) {
            $this->logService->write('Survey', $survey->getId(), $user);
        }
    }
}
