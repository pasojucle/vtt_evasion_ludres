<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use App\Entity\BikeRide;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Level;
use App\Entity\Respondent;
use App\Entity\Session;
use App\Entity\User;
use App\Service\CacheService;
use App\Service\LogService;
use App\Service\NotificationService;
use App\Service\SessionService;
use App\Service\SurveyService;
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
        private readonly SurveyService $surveyService,
        private readonly SessionService $sessionService,
    ) {
    }

    public function add(FormInterface $form, User $user, BikeRide $bikeRide): void
    {
        /** @var Session $session */
        $session = $form->get('session')->getData();
        $user->addSession($session);
        $responses = ($form->has('responses')) ? $form->get('responses')->getData() : null;
        if (AvailabilityEnum::REGISTERED === $session->getAVailability() && $responses && !empty($surveyResponses = $responses['surveyResponses'])) {
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
        /** @var Session $session */
        $session = $form->get('session')->getData();
        $responses = ($form->has('responses')) ? $form->get('responses')->getData() : null;
        $bikeRide = $session->getCluster()->getBikeRide();
        $user = $session->getUser();
        if ($responses && !empty($surveyResponses = $responses['surveyResponses'])) {
            $this->editSurveyResponses($user, $bikeRide, $surveyResponses, $session->getAvailability());
        }
        $this->entityManager->flush();
        $this->cacheService->deleteCacheIndex($session->getCluster());
        $this->confirmationSession->execute($session);
        $this->writeLog($bikeRide, $user);
    }

    public function addFromdmin(array $data, User $user, BikeRide $bikeRide): void
    {
        $userSession = new Session();
        $userCluster = $data['cluster'];
        if (null === $userCluster) {
            $userCluster = $this->sessionService->getCluster($bikeRide, $user, $bikeRide->getClusters());
        }
        $userSession->setUser($user)
            ->setCluster($userCluster);
        if ($bikeRide->getBikeRideType()->isNeedFramers() && $user->getLevel()->getType() === Level::TYPE_FRAME) {
            $userSession->setAvailability(AvailabilityEnum::REGISTERED);
        }
        $user->addSession($userSession);
        $responses = array_key_exists('responses', $data) ? $data['responses'] : null;
        if ($responses && !empty($surveyResponses = $responses['surveyResponses'])) {
            $this->addSurveyResponses($surveyResponses, $user, $bikeRide);
        }
        $this->entityManager->persist($userSession);
        $this->cacheService->deleteCacheIndex($userSession->getCluster());
        $this->entityManager->flush();
    }

    public function delete(Session $session): void
    {
        if ($survey = $session->getCluster()->getBikeRide()->getSurvey()) {
            $this->surveyService->deleteResponses($session->getUser(), $survey);
        }
        $this->cacheService->deleteCacheIndex($session->getCluster());
        $this->entityManager->remove($session);
        $this->entityManager->flush();
    }

    private function editSurveyResponses(User $user, BikeRide $bikeRide, array $surveyResponses, ?AvailabilityEnum $availability): void
    {
        if (AvailabilityEnum::REGISTERED !== $availability) {
            $this->surveyService->deleteResponses($user, $bikeRide->getSurvey());
            return;
        }

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
